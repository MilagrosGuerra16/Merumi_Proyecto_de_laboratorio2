<?php
// devolver respuesta en JSON
header("Content-Type: application/json");

include "conexion.php";

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

// verificar existencia de la reserva
$sqlCheck = "
    SELECT fecha 
    FROM reserva 
    WHERE id_reserva = ?
";
$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Reserva no encontrada"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// bloquear cancelacion de reservas pasadas
$fechaReserva = $row["fecha"];
$hoy = date("Y-m-d");

if ($fechaReserva < $hoy) {
    echo json_encode([
        "success" => false,
        "message" => "No se puede cancelar una reserva pasada"
    ]);
    exit;
}

// eliminar reserva
$sqlDelete = "DELETE FROM reserva WHERE id_reserva = ?";
$stmt = $conexion->prepare($sqlDelete);
$stmt->bind_param("i", $id_reserva);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error al eliminar la reserva"
    ]);
    exit;
}

// liberar mesa
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

// respuesta final
echo json_encode([
    "success" => true,
    "message" => "Reserva cancelada correctamente"
]);
?>
