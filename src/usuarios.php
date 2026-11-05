<?php
header("Content-Type: application/json");
require_once "db.php";
$r = $conn->query("SELECT u.id_usuario, u.nombre, u.apellido, u.correo, u.codigo_univ, u.tipo, f.nombre AS facultad, fn_total_prestamos_activos(u.id_usuario) AS prestamos_activos FROM usuario u LEFT JOIN facultad f ON f.id_facultad=u.id_facultad WHERE u.activo=1 ORDER BY u.id_usuario");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
