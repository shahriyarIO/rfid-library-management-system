<?php
include 'header.php';
include 'db.php';

$sql = "SELECT b.title, b.author, u.name, u.student_id, u.email, bb.borrow_date
        FROM borrowed_books bb
        JOIN books b ON bb.book_uid = b.rfid_uid
        JOIN users u ON bb.student_uid = u.uid
        WHERE bb.status = 'borrowed'
        ORDER BY bb.borrow_date DESC";

$result = $conn->query($sql);
?>

<h2>📤 Borrowed Books</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>Book Title</th>
            <th>Author</th>
            <th>Student Name</th>
            <th>Student ID</th>
            <th>Email</th>
            <th>Borrow Date</th>
            <th>Return Deadline</th>
            <th>Fine (Tk)</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): 
            $borrow_ts   = strtotime($row['borrow_date']);
            $deadline_ts = $borrow_ts + (3 * 24 * 60 * 60);
            $today       = time();
            $days_late   = floor(($today - $deadline_ts) / 86400);
            $fine        = ($days_late > 0) ? $days_late * 10 : 0;
        ?>
        <tr>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['author'] ? $row['author'] : '-'; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['student_id']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo date('d M Y', $borrow_ts); ?></td>
            <td><?php echo date('d M Y', $deadline_ts); ?></td>
            <td style="color:<?php echo $fine > 0 ? 'red' : 'green'; ?>; font-weight:bold;">
                Tk <?php echo $fine; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>
</body>
</html>