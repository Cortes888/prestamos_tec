<?php
header("Content-Type: application/json");
require_once "db.php";
$r = $conn->query("SELECT id_objeto, nombre, codigo_inv FROM objeto WHERE estado='disponible' ORDER BY nombre");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
