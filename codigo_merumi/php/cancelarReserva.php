<?php
// iniciar sesion
session_start();

// devolver respuesta en JSON
header("Content-Type: application/json");

include "conexion.php";

// validar sesion activa
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "success" => false,
        "message" => "No hay sesión"
    ]);
    exit;
}

// validar datos recibidos
if (!isset($_POST["id_reserva"], $_POST["id_mesa"])) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$id_reserva = intval($_POST["id_reserva"]);
$id_mesa    = intval($_POST["id_mesa"]);
$id_usuario = $_SESSION["id_usuario"];

// verificar que la reserva pertenezca al usuario
$sqlCheck = "
    SELECT id_reserva 
    FROM reserva 
    WHERE id_reserva = ? AND id_usuario = ?
";
$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("ii", $id_reserva, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Reserva no encontrada"
    ]);
    exit;
}

// eliminar reserva
$sqlDelete = "DELETE FROM reserva WHERE id_reserva = ?";
$stmt = $conexion->prepare($sqlDelete);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();

// liberar mesa
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

// respuesta final
echo json_encode(["success" => true]);
