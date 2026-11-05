<?php
header("Content-Type: application/json");
require_once "db.php";
$r = $conn->query("SELECT n.id_notificacion, n.asunto, n.correo_destino, n.enviado, n.fecha_envio, CONCAT(u.nombre,' ',u.apellido) AS usuario, o.nombre AS objeto FROM notificacion n JOIN prestamo p ON p.id_prestamo=n.id_prestamo JOIN objeto o ON o.id_objeto=p.id_objeto JOIN usuario u ON u.id_usuario=p.id_usuario ORDER BY n.id_notificacion DESC");
$rows = [];
while ($row = $r->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
$conn->close();
