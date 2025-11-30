<?php
session_start();
header("Content-Type: application/json");
require "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["success" => false, "message" => "No hay sesión"]);
    exit;
}

if (!isset($_POST["id_reserva"]) || !isset($_POST["id_mesa"])) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$id_reserva = $_POST["id_reserva"];
$id_mesa = $_POST["id_mesa"];
$id_usuario = $_SESSION["id_usuario"];

// 🔹 1) Verificar que la reserva le pertenece al usuario (seguridad)
$sqlCheck = "SELECT id_reserva FROM reserva WHERE id_reserva = ? AND id_usuario = ?";
$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("ii", $id_reserva, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Reserva no encontrada"]);
    exit;
}

// 🔹 2) Borrar la reserva
$sqlDelete = "DELETE FROM reserva WHERE id_reserva = ?";
$stmt = $conexion->prepare($sqlDelete);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();

// 🔹 3) Cambiar mesa a estado Libre (1)
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

echo json_encode(["success" => true]);
