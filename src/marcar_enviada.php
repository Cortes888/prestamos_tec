<?php
header("Content-Type: application/json");
require_once "db.php";
$body = json_decode(file_get_contents("php://input"), true);
$all = $body['all'] ?? false;
if ($all) {
    $conn->query("UPDATE notificacion SET enviado=1, fecha_envio=NOW() WHERE enviado=0");
    echo json_encode(["mensaje" => "Correos marcados: " . $conn->affected_rows]);
} else {
    $id = intval($body['id_notificacion'] ?? 0);
    $conn->query("UPDATE notificacion SET enviado=1, fecha_envio=NOW() WHERE id_notificacion=$id");
    echo json_encode(["mensaje" => "Notificacion marcada como enviada."]);
}
$conn->close();
