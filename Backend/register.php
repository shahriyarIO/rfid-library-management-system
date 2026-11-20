<?php
include 'header.php';
include 'db.php';

// ফর্ম সাবমিট হলে student save করো
if (isset($_POST['submit'])) {
    $new_uid = $_POST['new_uid'];
    $name    = $_POST['name'];
    $sid     = $_POST['student_id'];
    $email   = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO users (uid, role, name, student_id, email) VALUES (?, 'student', ?, ?, ?)");
    $stmt->bind_param('ssss', $new_uid, $name, $sid, $email);
    $stmt->execute();

    $conn->query("UPDATE temp_scan SET scan_type='admin', last_uid='' WHERE id=1");
}

$temp      = $conn->query("SELECT * FROM temp_scan WHERE id=1")->fetch_assoc();
$scan_type = $temp['scan_type'];
$last_uid  = $temp['last_uid'];
?>

<h2>🔐 Register Portal</h2>

<?php if ($scan_type !== 'admin' && $scan_type !== 'unregistered'): ?>

    <!-- Admin card ধরা হয়নি -->
    <div class="alert warning">
        <strong>🔒 Locked!</strong> Admin RFID card scanner এ ধরুন। পেজ অটো রিফ্রেশ হবে।
    </div>
    <script>setTimeout(function(){ location.reload(); }, 2000);</script>

<?php elseif ($scan_type === 'admin'): ?>

    <!-- Admin verified, student card এর অপেক্ষা -->
    <div class="alert success">
        <strong>🔓 Portal Unlocked!</strong> এখন নতুন Student এর RFID card scanner এ ধরুন।
    </div>
    <script>setTimeout(function(){ location.reload(); }, 2000);</script>

<?php elseif ($scan_type === 'unregistered'): ?>

    <!-- নতুন card পাওয়া গেছে, ফর্ম দেখাও -->
    <div class="alert info">
        <strong>🆕 New Card Detected!</strong> UID: <strong><?php echo $last_uid; ?></strong>
    </div>

    <?php if (isset($_POST['submit'])): ?>
        <div class="alert success">✅ Student registered successfully!</div>
    <?php endif; ?>

    <div class="portal-box">
        <h3>Student Information</h3>
        <form method="POST" action="register.php">
            <input type="hidden" name="new_uid" value="<?php echo $last_uid; ?>">

            <div class="form-group">
                <label>Full Name:</label>
                <input type="text" name="name" placeholder="Student এর নাম" required>
            </div>
            <div class="form-group">
                <label>Student ID:</label>
                <input type="text" name="student_id" placeholder="যেমন: STD2024001" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" placeholder="example@email.com">
            </div>

            <button type="submit" name="submit" class="btn">💾 Save Student</button>
        </form>
    </div>

<?php endif; ?>

</div>
</body>
</html>