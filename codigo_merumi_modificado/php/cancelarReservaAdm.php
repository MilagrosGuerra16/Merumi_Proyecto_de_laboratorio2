<?php

// Respuesta en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
require "conexion.php";

// Verifica que lleguen los datos necesarios
if (!isset($_POST["id_reserva"]) || !isset($_POST["id_mesa"])) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

// Convierte los valores a enteros
$id_reserva = intval($_POST["id_reserva"]);
$id_mesa    = intval($_POST["id_mesa"]);

// Busca la fecha de la reserva
$sqlCheck = "
    SELECT fecha 
    FROM reserva 
    WHERE id_reserva = ?
";

$stmt = $conexion->prepare($sqlCheck);
$stmt->bind_param("i", $id_reserva);
$stmt->execute();
$result = $stmt->get_result();

// Si la reserva no existe
if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Reserva no encontrada"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// Compara la fecha de la reserva con la fecha actual
$fechaReserva = $row["fecha"];
$hoy = date("Y-m-d");

// Bloquea la cancelación de reservas pasadas
if ($fechaReserva < $hoy) {
    echo json_encode([
        "success" => false,
        "message" => "No se puede cancelar una reserva pasada"
    ]);
    exit;
}

// Elimina la reserva
$sqlDelete = "DELETE FROM reserva WHERE id_reserva = ?";
$stmt = $conexion->prepare($sqlDelete);
$stmt->bind_param("i", $id_reserva);

// Error al eliminar
if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error al eliminar la reserva"
    ]);
    exit;
}

// Cambia el estado de la mesa a disponible
$sqlMesa = "UPDATE mesas SET id_estado = 1 WHERE id_mesas = ?";
$stmt = $conexion->prepare($sqlMesa);
$stmt->bind_param("i", $id_mesa);
$stmt->execute();

// Respuesta final
echo json_encode([
    "success" => true,
    "message" => "Reserva cancelada correctamente"
]);

?>
