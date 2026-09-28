<?php
header("Content-Type: application/json");
require_once "db.php";
$r = $conn->query("SELECT p.id_prestamo, CONCAT(u.nombre,' ',u.apellido) AS usuario, o.nombre AS objeto, 
p.fecha_prestamo, p.fecha_devolucion_esperada, p.fecha_devolucion_real, p.estado FROM prestamo p JOIN 
usuario u ON u.id_usuario=p.id_usuario JOIN objeto o ON o.id_objeto=p.id_objeto ORDER BY p.id_prestamo DESC");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
