<?php
include 'header.php';
include 'db.php';

$total    = $conn->query("SELECT COUNT(*) c FROM books")->fetch_assoc()['c'];
$shelf    = $conn->query("SELECT COUNT(*) c FROM books WHERE status='in_shelf'")->fetch_assoc()['c'];
$borrowed = $conn->query("SELECT COUNT(*) c FROM books WHERE status='borrowed'")->fetch_assoc()['c'];
$students = $conn->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch_assoc()['c'];

$temp = $conn->query("SELECT * FROM temp_scan WHERE id=1")->fetch_assoc();
?>

<h2>📊 Dashboard</h2>

<div class="stat-grid">
    <div class="stat-card">
        <div class="num"><?php echo $total; ?></div>
        <div class="label">Total Books</div>
    </div>
    <div class="stat-card green">
        <div class="num"><?php echo $shelf; ?></div>
        <div class="label">In Shelf</div>
    </div>
    <div class="stat-card red">
        <div class="num"><?php echo $borrowed; ?></div>
        <div class="label">Borrowed</div>
    </div>
    <div class="stat-card amber">
        <div class="num"><?php echo $students; ?></div>
        <div class="label">Students</div>
    </div>
</div>

<div class="scan-status-box">
    <h3>📡 Scanner Status</h3>
    <p>Last UID: <span class="uid"><?php echo $temp['last_uid'] ? $temp['last_uid'] : '---'; ?></span></p>
    <p>Type: <span class="badge <?php echo $temp['scan_type']; ?>"><?php echo strtoupper($temp['scan_type']); ?></span></p>
    <p style="color:#aaa; font-size:13px; margin-top:8px;">Last scan: <?php echo $temp['scan_time']; ?></p>
</div>

</div>
</body>
</html>