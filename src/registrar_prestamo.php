<?php
header("Content-Type: application/json");
require_once "db.php";
$body = json_decode(file_get_contents("php://input"), true);
$id_usuario = intval($body['id_usuario'] ?? 0);
$id_objeto  = intval($body['id_objeto']  ?? 0);
$dias       = intval($body['dias']       ?? 3);
$obs        = $conn->real_escape_string($body['obs'] ?? '');
if (!$id_usuario || !$id_objeto) { echo json_encode(["error" => "Faltan datos"]); exit; }
$stmt = $conn->prepare("CALL sp_registrar_prestamo(?, ?, ?, ?, @id_p, @msg)");
$stmt->bind_param("iiis", $id_usuario, $id_objeto, $dias, $obs);
$stmt->execute();
$stmt->close();
$r = $conn->query("SELECT @id_p AS id_prestamo, @msg AS mensaje");
$row = $r->fetch_assoc();
echo json_encode(["mensaje" => $row['mensaje'], "id_prestamo" => $row['id_prestamo']]);
$conn->close();
