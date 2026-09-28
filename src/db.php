<?php
$host     = "localhost";
$usuario  = "root";
$password = "";
$base     = "prestamos_tec";
$conn = new mysqli($host, $usuario, $password, $base);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Conexion fallida: " . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");
