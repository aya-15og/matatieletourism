<?php
/**
 * Visitor Tracker - Admin Dashboard
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../includes/config.php'; // $pdo must be defined here

// Sanitize input
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Filters
$date_filter   = isset($_GET['date_filter']) ? sanitize_input($_GET['date_filter']) : '7days';
$device_filter = isset($_GET['device_filter']) ? sanitize_input($_GET['device_filter']) : 'all';
$bot_filter    = isset($_GET['bot_filter']) ? sanitize_input($_GET['bot_filter']) : 'legitimate';

// Build SQL filters
switch ($date_filter) {
    case '24hours': $date_sql = "AND visit_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"; break;
    case '7days':   $date_sql = "AND visit_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; break;
    case '30days':  $date_sql = "AND visit_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)"; break;
    case '90days':  $date_sql = "AND visit_time >= DATE_SUB(NOW(), INTERVAL 90 DAY)"; break;
    case 'all':     $date_sql = ""; break;
    default:        $date_sql = "AND visit_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
}

$device_sql = ($device_filter !== 'all') ? "AND device_type = :device" : "";

if ($bot_filter === 'legitimate') {
    $bot_sql = "AND is_bot = 0";
} elseif ($bot_filter === 'bots') {
    $bot_sql = "AND is_bot = 1";
} else {
    $bot_sql = "";
}

// Stats initialization
$stats = [
    'total_visits' => 0,
    'unique_visitors' => 0,
    'returning_visitors' => 0,
    'new_visitors' => 0,
    'bot_visits' => 0,
    'device_distribution' => [],
    'top_countries' => [],
    'top_cities' => [],
    'top_pages' => [],
    'recent_visitors' => []
];

// Helper
function run_query(PDO $pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

$params = [];
if ($device_sql) $params[':device'] = $device_filter;

// Total visits
$result = run_query($pdo, "SELECT COUNT(*) as total FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql", $params);
$stats['total_visits'] = $result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Unique visitors
$result = run_query($pdo, "SELECT COUNT(DISTINCT visitor_id) as unique_count FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql", $params);
$stats['unique_visitors'] = $result->fetch(PDO::FETCH_ASSOC)['unique_count'] ?? 0;

// Returning visitors
$sql_returning = "SELECT COUNT(*) as returning_count FROM (
    SELECT visitor_id FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql GROUP BY visitor_id HAVING COUNT(*) > 1
) AS temp";
$result = run_query($pdo, $sql_returning, $params);
$stats['returning_visitors'] = $result->fetch(PDO::FETCH_ASSOC)['returning_count'] ?? 0;

// New visitors
$stats['new_visitors'] = max(0, $stats['unique_visitors'] - $stats['returning_visitors']);

// Bot visits
$result = run_query($pdo, "SELECT COUNT(*) as bot_count FROM visitors WHERE 1=1 $date_sql $device_sql AND is_bot = 1", $params);
$stats['bot_visits'] = $result->fetch(PDO::FETCH_ASSOC)['bot_count'] ?? 0;

// Device distribution
$result = run_query($pdo, "SELECT device_type, COUNT(*) as count FROM visitors WHERE 1=1 $date_sql $bot_sql GROUP BY device_type ORDER BY count DESC", $params);
$stats['device_distribution'] = $result->fetchAll(PDO::FETCH_ASSOC);

// Top countries
$result = run_query($pdo, "SELECT country, COUNT(*) as count FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql AND country != 'Unknown' GROUP BY country ORDER BY count DESC LIMIT 10", $params);
$stats['top_countries'] = $result->fetchAll(PDO::FETCH_ASSOC);

// Top cities
$result = run_query($pdo, "SELECT city, country, COUNT(*) as count FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql AND city != 'Unknown' GROUP BY city, country ORDER BY count DESC LIMIT 10", $params);
$stats['top_cities'] = $result->fetchAll(PDO::FETCH_ASSOC);

// Top pages
$result = run_query($pdo, "SELECT page_visited, COUNT(*) as count FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql GROUP BY page_visited ORDER BY count DESC LIMIT 10", $params);
$stats['top_pages'] = $result->fetchAll(PDO::FETCH_ASSOC);

// Recent visitors
$result = run_query($pdo, "SELECT visitor_id, ip_address, device_type, country, city, visit_time, is_bot FROM visitors WHERE 1=1 $date_sql $device_sql $bot_sql ORDER BY visit_time DESC LIMIT 20", $params);
$stats['recent_visitors'] = $result->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Visitor Tracker Dashboard</title>
<style>
/* === BASE STYLES === */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;padding:20px;}
.container{max-width:1400px;margin:0 auto;}
header{background:white;padding:30px;border-radius:10px;margin-bottom:30px;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
h1{color:#333;margin-bottom:20px;font-size:28px;}
.back-btn{display:inline-block;margin-bottom:20px;padding:10px 20px;background:#48bb78;color:white;border:none;border-radius:5px;text-decoration:none;font-weight:600;transition:0.3s;}
.back-btn:hover{background:#38a169;}
.filters{display:flex;gap:20px;flex-wrap:wrap;align-items:center;}
.filter-group{display:flex;gap:10px;align-items:center;}
.filter-group label{font-weight:600;color:#555;}
.filter-group select,.filter-group button{padding:8px 12px;border:1px solid #ddd;border-radius:5px;font-size:14px;cursor:pointer;}
.filter-group button{background:#667eea;color:white;border:none;font-weight:600;transition:0.3s;}
.filter-group button:hover{background:#764ba2;}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px;}
.stat-card{background:white;padding:25px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);border-left:5px solid #667eea;}
.stat-card.unique{border-left-color:#48bb78;}
.stat-card.returning{border-left-color:#ed8936;}
.stat-card.new{border-left-color:#4299e1;}
.stat-card.bots{border-left-color:#f56565;}
.stat-label{color:#999;font-size:14px;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px;}
.stat-value{font-size:36px;font-weight:bold;color:#333;}
.charts-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;margin-bottom:30px;}
.chart-card{background:white;padding:25px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.chart-title{font-size:18px;font-weight:600;color:#333;margin-bottom:20px;}
.chart-item{display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;padding-bottom:15px;border-bottom:1px solid #eee;}
.chart-item:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0;}
.chart-label{font-weight:500;color:#555;flex:1;}
.chart-bar{flex:2;height:25px;background:linear-gradient(90deg,#667eea,#764ba2);border-radius:5px;margin:0 15px;position:relative;transition:width 1s ease;}
.chart-value{font-weight:600;color:#333;min-width:50px;text-align:right;}
.table-card{background:white;padding:25px;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,0.1);margin-bottom:30px;overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
th{background:#f7fafc;padding:15px;text-align:left;font-weight:600;color:#333;border-bottom:2px solid #e2e8f0;}
td{padding:12px 15px;border-bottom:1px solid #e2e8f0;}
tr:hover{background:#f7fafc;}
.badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.badge.bot{background:#fed7d7;color:#c53030;}
.badge.human{background:#c6f6d5;color:#22543d;}
.badge.mobile{background:#bee3f8;color:#2c5282;}
.badge.desktop{background:#feebc8;color:#7c2d12;}
.badge.tablet{background:#e6fffa;color:#234e52;}
footer{text-align:center;color:white;margin-top:30px;font-size:14px;}
@media(max-width:768px){.filters{flex-direction:column;align-items:flex-start;}.filter-group{width:100%;}.filter-group select,.filter-group button{width:100%;}.charts-grid{grid-template-columns:1fr;}.stat-value{font-size:28px;}}
</style>
</head>
<body>
<div class="container">
<header>
<h1>📊 Visitor Tracker Dashboard</h1>
<a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<form method="GET" class="filters">
<div class="filter-group">
<label for="date_filter">Time Period:</label>
<select name="date_filter" id="date_filter" onchange="this.form.submit()">
<?php
$dates=['24hours'=>'Last 24 Hours','7days'=>'Last 7 Days','30days'=>'Last 30 Days','90days'=>'Last 90 Days','all'=>'All Time'];
foreach($dates as $key=>$label){$selected=($date_filter===$key)?'selected':'';echo"<option value=\"$key\" $selected>$label</option>";}
?>
</select>
</div>
<div class="filter-group">
<label for="device_filter">Device:</label>
<select name="device_filter" id="device_filter" onchange="this.form.submit()">
<?php
$devices=['all'=>'All Devices','Desktop'=>'Desktop','Mobile'=>'Mobile','Tablet'=>'Tablet'];
foreach($devices as $key=>$label){$selected=($device_filter===$key)?'selected':'';echo"<option value=\"$key\" $selected>$label</option>";}
?>
</select>
</div>
<div class="filter-group">
<label for="bot_filter">Traffic Type:</label>
<select name="bot_filter" id="bot_filter" onchange="this.form.submit()">
<?php
$bots=['legitimate'=>'Legitimate Users','bots'=>'Bots Only','all'=>'All Traffic'];
foreach($bots as $key=>$label){$selected=($bot_filter===$key)?'selected':'';echo"<option value=\"$key\" $selected>$label</option>";}
?>
</select>
</div>
</form>
</header>

<!-- STAT CARDS -->
<div class="stats-grid">
<div class="stat-card"><div class="stat-label">Total Visits</div><div class="stat-value"><?php echo number_format($stats['total_visits']); ?></div></div>
<div class="stat-card unique"><div class="stat-label">Unique Visitors</div><div class="stat-value"><?php echo number_format($stats['unique_visitors']); ?></div></div>
<div class="stat-card returning"><div class="stat-label">Returning Visitors</div><div class="stat-value"><?php echo number_format($stats['returning_visitors']); ?></div></div>
<div class="stat-card new"><div class="stat-label">New Visitors</div><div class="stat-value"><?php echo number_format($stats['new_visitors']); ?></div></div>
<?php if($bot_filter==='all'): ?>
<div class="stat-card bots"><div class="stat-label">Bot Visits</div><div class="stat-value"><?php echo number_format($stats['bot_visits']); ?></div></div>
<?php endif; ?>
</div>

<!-- CHARTS -->
<div class="charts-grid">
<div class="chart-card">
<div class="chart-title">📱 Device Distribution</div>
<?php if(!empty($stats['device_distribution'])): 
$total=array_sum(array_column($stats['device_distribution'],'count'));
foreach($stats['device_distribution'] as $d): 
$pct=($total>0)?($d['count']/$total)*100:0;
?>
<div class="chart-item">
<div class="chart-label"><?php echo $d['device_type']; ?></div>
<div class="chart-bar" style="width:<?php echo round($pct,2); ?>%;"></div>
<div class="chart-value"><?php echo $d['count']; ?></div>
</div>
<?php endforeach; else: ?><p style="color:#999;">No data available</p><?php endif; ?>
</div>

<div class="chart-card">
<div class="chart-title">🌍 Top Countries</div>
<?php if(!empty($stats['top_countries'])): 
$total=array_sum(array_column($stats['top_countries'],'count'));
foreach($stats['top_countries'] as $c):
$pct=($total>0)?($c['count']/$total)*100:0;
?>
<div class="chart-item">
<div class="chart-label"><?php echo $c['country']; ?></div>
<div class="chart-bar" style="width:<?php echo round($pct,2); ?>%;"></div>
<div class="chart-value"><?php echo $c['count']; ?></div>
</div>
<?php endforeach; else: ?><p style="color:#999;">No data available</p><?php endif; ?>
</div>
</div>

<!-- RECENT VISITORS TABLE -->
<div class="table-card">
<h2 style="margin-bottom:15px;">📝 Recent Visitors</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>IP Address</th>
<th>Device</th>
<th>Country</th>
<th>City</th>
<th>Time</th>
<th>Type</th>
</tr>
</thead>
<tbody>
<?php foreach($stats['recent_visitors'] as $v): ?>
<tr>
<td><?php echo $v['visitor_id']; ?></td>
<td><?php echo $v['ip_address']; ?></td>
<td>
<span class="badge <?php echo strtolower($v['device_type']); ?>"><?php echo $v['device_type']; ?></span>
</td>
<td><?php echo $v['country']; ?></td>
<td><?php echo $v['city']; ?></td>
<td><?php echo $v['visit_time']; ?></td>
<td>
<span class="badge <?php echo $v['is_bot'] ? 'bot':'human'; ?>"><?php echo $v['is_bot']?'Bot':'Human'; ?></span>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<footer>
&copy; <?php echo date('Y'); ?> Matatiele Online | Visitor Dashboard
</footer>
</div>
</body>
</html>
