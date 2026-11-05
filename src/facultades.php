<?php
header("Content-Type: application/json");
require_once "db.php";
$r = $conn->query("SELECT id_facultad, nombre FROM facultad ORDER BY nombre");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
