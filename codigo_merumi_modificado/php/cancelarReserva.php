<?php
// Inicia la sesión para acceder al usuario logueado
session_start();

// Respuesta en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
require "conexion.php";

// Verifica que haya una sesión iniciada
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["success" => false, "message" => "No hay sesión"]);
    exit;
}

// Verifica que lleguen los datos necesarios
if (!isset($_POST["id_reserva"]) || !isset($_POST["id_mesa"])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

// Guarda los datos recibidos
$id_reserva = $_POST["id_reserva"];
$id_mesa = $_POST["id_mesa"];
$id_usuario = $_SESSION["id_usuario"];

// 1) Verifica que la reserva exista y pertenezca al usuario logueado
$sqlCheck = "SELECT id_reserva FROM reserva WHERE id_reserva = ? AND id_usuario = ?";
$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("ii", $id_reserva, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Si no encuentra la reserva, muestra error
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Reserva no encontrada"]);
    exit;
}

// 2) Elimina la reserva
$sqlDelete = "DELETE FROM reserva WHERE id_reserva = ?";
$stmt = $conexion->prepare($sqlDelete);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();

// 3) Libera la mesa cambiando su estado a disponible (1)
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

// Respuesta final de éxito
echo json_encode(["success" => true]);
