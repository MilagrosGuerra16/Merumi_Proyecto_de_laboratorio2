<?php
header("Content-Type: application/json");
require "conexion.php";

// =====================
// VALIDACIÓN DATOS
// =====================
if (!isset($_POST["id_reserva"]) || !isset($_POST["id_mesa"])) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$id_reserva = intval($_POST["id_reserva"]);
$id_mesa    = intval($_POST["id_mesa"]);

// =====================
// VERIFICAR RESERVA
// =====================
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

// =====================
// BLOQUEO DE RESERVAS PASADAS
// =====================
$fechaReserva = $row["fecha"];
$hoy = date("Y-m-d");

if ($fechaReserva < $hoy) {
    echo json_encode([
        "success" => false,
        "message" => "No se puede cancelar una reserva pasada"
    ]);
    exit;
}

// =====================
// ELIMINAR RESERVA
// =====================
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

// =====================
// LIBERAR MESA
// =====================
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

echo json_encode([
    "success" => true,
    "message" => "Reserva cancelada correctamente"
]);
?>
