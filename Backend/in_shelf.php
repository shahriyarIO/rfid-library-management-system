<?php
include 'header.php';
include 'db.php';

$result = $conn->query("SELECT * FROM books WHERE status='in_shelf' ORDER BY title");
?>

<h2>📖 Books In Shelf</h2>

<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>RFID UID</th>
            <th>Book Title</th>
            <th>Author</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo $i++; ?></td>
            <td><?php echo $row['rfid_uid']; ?></td>
            <td><?php echo $row['title']; ?></td>
            <td><?php echo $row['author'] ? $row['author'] : '-'; ?></td>
            <td><span class="badge-shelf">In Shelf</span></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</div>
</body>
</html>