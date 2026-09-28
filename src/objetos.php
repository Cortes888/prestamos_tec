<?php
header("Content-Type: application/json");
require_once "db.php";
// Añadimos: WHERE o.estado != 'baja'
$r = $conn->query("SELECT o.id_objeto, o.nombre, o.codigo_inv, o.estado, o.descripcion, c.nombre AS categoria, (SELECT COUNT(*) FROM prestamo WHERE id_objeto=o.id_objeto) AS total_prestamos FROM objeto o JOIN categoria c ON c.id_categoria=o.id_categoria WHERE o.estado != 'baja' ORDER BY o.id_objeto");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
?>