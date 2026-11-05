<?php
header("Content-Type: application/json");
require_once "db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // AHORA SÍ: Borrado real de la base de datos
    $sql = "DELETE FROM objeto WHERE id_objeto = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(["mensaje" => "Objeto eliminado permanentemente"]);
    } else {
        echo json_encode(["error" => $conn->error]);
    }
}
$conn->close();