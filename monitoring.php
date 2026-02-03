<?php
$conn = new mysqli("localhost", "root", "", "employees");
$result = $conn->query("
    SELECT e.firstName, e.lastName, a.date, a.time_in, a.time_out
    FROM attendance a
    JOIN employees e ON e.id = a.employee_id
    ORDER BY a.created_at DESC
");
?>
<table class="table table-bordered">
<tr>
    <th>Name</th>
    <th>Date</th>
    <th>Time In</th>
    <th>Time Out</th>
</tr>
<?php while($r = $result->fetch_assoc()): ?>
<tr>
    <td><?= $r['firstName']." ".$r['lastName'] ?></td>
    <td><?= $r['date'] ?></td>
    <td><?= $r['time_in'] ?? '-' ?></td>
    <td><?= $r['time_out'] ?? '-' ?></td>
</tr>
<?php endwhile; ?>
</table>
