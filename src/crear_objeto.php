<?php
header("Content-Type: application/json");
require_once "db.php";
$body = json_decode(file_get_contents("php://input"), true);
$nombre      = $conn->real_escape_string($body['nombre']      ?? '');
$codigo_inv  = $conn->real_escape_string($body['codigo_inv']  ?? '');
$id_categoria= intval($body['id_categoria'] ?? 0);
$descripcion = $conn->real_escape_string($body['descripcion'] ?? '');
if (!$nombre || !$codigo_inv || !$id_categoria) { echo json_encode(["error" => "Faltan datos"]); exit; }
$sql = "INSERT INTO objeto (nombre, descripcion, codigo_inv, id_categoria) VALUES ('$nombre','$descripcion','$codigo_inv',$id_categoria)";
if ($conn->query($sql)) {
    echo json_encode(["mensaje" => "Objeto agregado correctamente.", "id" => $conn->insert_id]);
} else {
    echo json_encode(["error" => $conn->error]);
}
$conn->close();
