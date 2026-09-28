<?php
header("Content-Type: application/json");
require_once "db.php";
$body = json_decode(file_get_contents("php://input"), true);
$id_prestamo = intval($body['id_prestamo'] ?? 0);
$obs = $conn->real_escape_string($body['obs'] ?? 'Sin novedades');
if (!$id_prestamo) { echo json_encode(["error" => "Falta id_prestamo"]); exit; }
$stmt = $conn->prepare("CALL sp_registrar_devolucion(?, ?, @msg)");
$stmt->bind_param("is", $id_prestamo, $obs);
$stmt->execute();
$stmt->close();
$r = $conn->query("SELECT @msg AS mensaje");
$row = $r->fetch_assoc();
echo json_encode(["mensaje" => $row['mensaje']]);
$conn->close();
