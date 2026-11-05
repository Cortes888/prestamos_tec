<?php
header("Content-Type: application/json");
require_once "db.php";
$body = json_decode(file_get_contents("php://input"), true);
$nombre      = $conn->real_escape_string($body['nombre']      ?? '');
$apellido    = $conn->real_escape_string($body['apellido']    ?? '');
$correo      = $conn->real_escape_string($body['correo']      ?? '');
$codigo_univ = $conn->real_escape_string($body['codigo_univ'] ?? '');
$tipo        = $conn->real_escape_string($body['tipo']        ?? 'estudiante');
$id_facultad = intval($body['id_facultad'] ?? 0);
if (!$nombre || !$correo || !$codigo_univ) { echo json_encode(["error" => "Faltan datos obligatorios"]); exit; }
$fac = $id_facultad ? $id_facultad : "NULL";
$sql = "INSERT INTO usuario (nombre, apellido, correo, codigo_univ, tipo, id_facultad) VALUES ('$nombre','$apellido','$correo','$codigo_univ','$tipo',$fac)";
if ($conn->query($sql)) {
    echo json_encode(["mensaje" => "Usuario creado correctamente.", "id" => $conn->insert_id]);
} else {
    echo json_encode(["error" => $conn->error]);
}
$conn->close();
