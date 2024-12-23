<?php
session_start();
include '../includes/db.php';
if ($_SESSION['role'] !== 'admin') {
    echo "Unauthorized access!";
    exit();
}
$result = $conn->query("SELECT * FROM posts");
while ($row = $result->fetch_assoc()) {
    echo "<div><h3>{$row['title']}</h3><p>{$row['content']}</p></div>";
}
?>
