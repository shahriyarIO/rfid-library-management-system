<?php
include 'db.php';

if(!isset($_GET['uid'])) {
    echo "No UID";
    exit;
}

$uid = strtoupper(trim($_GET['uid']));

// is this UID registered to a user?
$userCheck = $conn->query("SELECT role FROM users WHERE uid='$uid'");

// is this UID associated with a book?
$bookCheck = $conn->query("SELECT status FROM books WHERE rfid_uid='$uid'");

if($userCheck->num_rows > 0) {
    // ── User RFID ──
    $role = $userCheck->fetch_assoc()['role'];
    $conn->query("UPDATE temp_scan SET last_uid='$uid', scan_type='$role', scan_time=NOW() WHERE id=1");
    echo "User:$role";

} elseif($bookCheck->num_rows > 0) {
    // ── Book RFID ──
    $book = $bookCheck->fetch_assoc();
    
    //
    $temp = $conn->query("SELECT last_uid, scan_type FROM temp_scan WHERE id=1")->fetch_assoc();

    if($temp['scan_type'] === 'student') {
        $student_uid = $temp['last_uid'];

        // is this book already borrowed?
        if($book['status'] === 'borrowed') {
            $conn->query("UPDATE temp_scan SET scan_type='none' WHERE id=1");
            echo "Already Borrowed";
        } else {
            // Borrow the book
            $conn->query("INSERT INTO borrowed_books (book_uid, student_uid) VALUES ('$uid', '$student_uid')");
            $conn->query("UPDATE books SET status='borrowed' WHERE rfid_uid='$uid'");
            $conn->query("UPDATE temp_scan SET scan_type='none', last_uid='' WHERE id=1");
            echo "Borrowed OK";
        }
    } else {
        // Student ছাড়া book scan
        $conn->query("UPDATE temp_scan SET last_uid='$uid', scan_type='book', scan_time=NOW() WHERE id=1");
        echo "Book scanned, no student";
    }

} else {
    // ── অজানা card ──
    $conn->query("UPDATE temp_scan SET last_uid='$uid', scan_type='unregistered', scan_time=NOW() WHERE id=1");
    echo "Unregistered";
}
?>