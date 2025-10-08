<?php
session_start();
include("db.php");

// Security
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ---- KPIs ----
$totalDonors = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM donors"))[0];
$totalPatients = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM patients"))[0];
$totalHospitals = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM hospitals"))[0];
$totalBloodbanks = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bloodbanks"))[0];
$totalRequests = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM requests"))[0];

// ---- Inventory ----
$sql = "SELECT blood_group, status, COUNT(*) as total FROM blood_inventory GROUP BY blood_group, status";
$res = mysqli_query($conn, $sql);
$inventory = [];
while ($row = mysqli_fetch_assoc($res)) {
    $inventory[$row['blood_group']][$row['status']] = $row['total'];
}
$bloodGroups = [];
$availableData = [];
$usedData = [];
$expiredData = [];
foreach ($inventory as $group => $status) {
    $bloodGroups[] = $group;
    $availableData[] = $status['available'] ?? 0;
    $usedData[] = $status['used'] ?? 0;
    $expiredData[] = $status['expired'] ?? 0;
}

// ---- Requests ----
$reqCounts = [];
$rq = mysqli_query($conn, "SELECT status, COUNT(*) as total FROM requests GROUP BY status");
while ($r = mysqli_fetch_assoc($rq)) {
    $reqCounts[$r['status']] = $r['total'];
}

// ---- Donations (7 days) ----
$dates = [];
$donations = [];
$dq = mysqli_query($conn, "SELECT collection_date, COUNT(*) as total 
                           FROM blood_inventory 
                           WHERE collection_date >= CURDATE() - INTERVAL 7 DAY
                           GROUP BY collection_date ORDER BY collection_date");
while ($d = mysqli_fetch_assoc($dq)) {
    $dates[] = $d['collection_date'];
    $donations[] = $d['total'];
}

// ---- Patients vs Hospitals ----
$hospitals = [];
$patients = [];
$pq = mysqli_query($conn, "SELECT h.name, COUNT(p.patient_id) as total
                           FROM hospitals h LEFT JOIN patients p 
                           ON h.hospital_id = p.hospital_id
                           GROUP BY h.hospital_id");
while ($p = mysqli_fetch_assoc($pq)) {
    $hospitals[] = $p['name'];
    $patients[] = $p['total'];
}

// ---- Donors by Blood Group ----
$donorGroups = [];
$donorCounts = [];
$dres = mysqli_query($conn, "SELECT blood_group, COUNT(*) as total FROM donors GROUP BY blood_group");
while ($dn = mysqli_fetch_assoc($dres)) {
    $donorGroups[] = $dn['blood_group'];
    $donorCounts[] = $dn['total'];
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Blood Bank Analytics Dashboard</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<style>
body { font-family: Arial, sans-serif; background: #f8f9fa; margin:0; }
header { background:#c82333; color:white; padding:20px; text-align:center; font-size:24px; font-weight:bold; }
.container { width:95%; margin:20px auto; }

/* KPI Cards */
.kpis { display:flex; flex-wrap:wrap; gap:15px; margin-bottom:20px; }
.kpi { flex:1; min-width:150px; background:white; padding:15px; text-align:center; border-radius:10px; 
       box-shadow:0 4px 10px rgba(0,0,0,0.1); transition: transform 0.2s; }
.kpi:hover { transform:scale(1.05); }
.kpi h2 { color:#c82333; margin:5px 0; }

/* Buttons */
.buttons { text-align:center; margin:20px; }
.buttons button { margin:5px; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; background:#c82333; color:white; }
.buttons button.active { background:#007bff; }

/* Charts */
.chart-container { display:none; width:90%; margin:auto; background:white; padding:20px; border-radius:10px;
                   box-shadow:0 3px 8px rgba(0,0,0,0.1); }
canvas { width:100% !important; height:400px !important; }

/* Table */
.table-container { width:90%; margin:20px auto; background:white; padding:20px; border-radius:10px; 
                   box-shadow:0 3px 8px rgba(0,0,0,0.1); }
</style>
</head>
<body>
<header>📊 Blood Bank Analytics Dashboard</header>

<div class="container">

  <!-- KPIs -->
  <div class="kpis">
    <div class="kpi"><h2><?php echo $totalDonors; ?></h2><p>Donors</p></div>
    <div class="kpi"><h2><?php echo $totalPatients; ?></h2><p>Patients</p></div>
    <div class="kpi"><h2><?php echo $totalHospitals; ?></h2><p>Hospitals</p></div>
    <div class="kpi"><h2><?php echo $totalBloodbanks; ?></h2><p>Blood Banks</p></div>
    <div class="kpi"><h2><?php echo $totalRequests; ?></h2><p>Total Requests</p></div>
  </div>

  <!-- Interactive Table -->
  <div class="table-container">
    <table id="summaryTable" class="display">
      <thead><tr><th>Blood Group</th><th>Available</th><th>Used</th><th>Expired</th></tr></thead>
      <tbody>
        <?php foreach($bloodGroups as $i=>$g): ?>
          <tr>
            <td><?= $g ?></td>
            <td><?= $availableData[$i] ?></td>
            <td><?= $usedData[$i] ?></td>
            <td><?= $expiredData[$i] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Buttons -->
  <div class="buttons">
    <button onclick="showChart('inventory')">🩸 Inventory</button>
    <button onclick="showChart('requests')">📋 Requests</button>
    <button onclick="showChart('donations')">📈 Donations Trend</button>
    <button onclick="showChart('patients')">🏥 Patients vs Hospitals</button>
    <button onclick="showChart('donors')">👥 Donors Distribution</button>
    <button onclick="showChart('live')">⚡ Live Inventory</button>
  </div>

  <!-- Charts -->
  <div id="inventory" class="chart-container"><canvas id="invChart"></canvas></div>
  <div id="requests" class="chart-container"><canvas id="reqChart"></canvas></div>
  <div id="donations" class="chart-container"><canvas id="donChart"></canvas></div>
  <div id="patients" class="chart-container"><canvas id="patChart"></canvas></div>
  <div id="donors" class="chart-container"><canvas id="donorChart"></canvas></div>
  <div id="live" class="chart-container"><canvas id="liveChart"></canvas></div>

</div>

<script>
// Toggle chart display
function showChart(id){
  document.querySelectorAll('.chart-container').forEach(c => c.style.display='none');
  document.getElementById(id).style.display='block';
  document.querySelectorAll('.buttons button').forEach(b=>b.classList.remove('active'));
  event.target.classList.add('active');
}
$(document).ready(()=> $('#summaryTable').DataTable());

// Inventory Chart
new Chart(document.getElementById('invChart'), {
  type:'bar',
  data:{
    labels: <?php echo json_encode($bloodGroups); ?>,
    datasets:[
      {label:'Available', data:<?php echo json_encode($availableData); ?>, backgroundColor:'green'},
      {label:'Used', data:<?php echo json_encode($usedData); ?>, backgroundColor:'orange'},
      {label:'Expired', data:<?php echo json_encode($expiredData); ?>, backgroundColor:'red'}
    ]
  },
  options:{plugins:{title:{display:true, text:'Blood Inventory Summary'}}, responsive:true}
});

// Requests Pie
new Chart(document.getElementById('reqChart'), {
  type:'pie',
  data:{
    labels: <?php echo json_encode(array_keys($reqCounts)); ?>,
    datasets:[{data: <?php echo json_encode(array_values($reqCounts)); ?>, backgroundColor:['orange','green','red']}]
  },
  options:{plugins:{title:{display:true, text:'Requests Status Distribution'}}}
});

// Donations Line
new Chart(document.getElementById('donChart'), {
  type:'line',
  data:{
    labels: <?php echo json_encode($dates); ?>,
    datasets:[{label:'Donations', data:<?php echo json_encode($donations); ?>, borderColor:'blue', tension:0.3}]
  },
  options:{plugins:{title:{display:true, text:'Donations in Last 7 Days'}}, responsive:true}
});

// Patients per hospital
new Chart(document.getElementById('patChart'), {
  type:'bar',
  data:{
    labels: <?php echo json_encode($hospitals); ?>,
    datasets:[{label:'Patients', data:<?php echo json_encode($patients); ?>, backgroundColor:'purple'}]
  },
  options:{plugins:{title:{display:true, text:'Patients Registered per Hospital'}}}
});

// Donors distribution
new Chart(document.getElementById('donorChart'), {
  type:'doughnut',
  data:{
    labels: <?php echo json_encode($donorGroups); ?>,
    datasets:[{data:<?php echo json_encode($donorCounts); ?>, 
      backgroundColor:['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40']}]
  },
  options:{plugins:{title:{display:true, text:'Donors by Blood Group'}}}
});

// Live Inventory Line (auto-refresh simulation)
let liveChart = new Chart(document.getElementById('liveChart'), {
  type:'line',
  data:{labels: <?php echo json_encode($bloodGroups); ?>,
    datasets:[
      {label:'Available', data:<?php echo json_encode($availableData); ?>, borderColor:'green', fill:false},
      {label:'Used', data:<?php echo json_encode($usedData); ?>, borderColor:'orange', fill:false},
      {label:'Expired', data:<?php echo json_encode($expiredData); ?>, borderColor:'red', fill:false}
    ]},
  options:{plugins:{title:{display:true, text:'Live Inventory (Auto Refresh)'}}}
});

// Auto-refresh every 5s (simulated demo)
setInterval(()=>{
  liveChart.data.datasets.forEach(ds=>{
    ds.data = ds.data.map(v=> v + Math.floor(Math.random()*3-1)); // simulate +/-1
  });
  liveChart.update();
}, 5000);

// Show first chart
showChart('inventory');
</script>
</body>
</html>
