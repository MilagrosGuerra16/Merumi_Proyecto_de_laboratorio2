<?php
// Arranca la sesión para saber qué usuario está logueado
session_start();

// El archivo va a devolver datos en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
require "conexion.php";

// Si no hay un usuario logueado, no devuelve nada
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([]);
    exit;
}

// Guarda el id del usuario logueado
$id_usuario = $_SESSION["id_usuario"];

// Consulta para traer las reservas del usuario
$sql = "
SELECT 
    r.id_reserva,               // id de la reserva
    r.id_mesa,                  // id de la mesa
    m.nombre AS nombre_mesa,    // nombre de la mesa
    r.fecha,                    // fecha de la reserva
    t.nombre AS nombre_turno,   // nombre del turno
    e.descripcion AS estado     // estado de la reserva
FROM reserva r
INNER JOIN mesas m ON r.id_mesa = m.id_mesas      // une reserva con mesa
INNER JOIN turnos t ON r.id_turno = t.id_turnos   // une reserva con turno
INNER JOIN estado e ON r.id_estado = e.id_estado  // une reserva con estado
WHERE r.id_usuario = ?                            // solo reservas del usuario
AND (
    r.fecha > CURDATE()                           // fechas futuras
    OR (r.fecha = CURDATE() AND t.fin_hora > CURTIME()) // o hoy pero no terminado
)
ORDER BY r.fecha ASC                              // ordenadas por fecha
";

// Prepara la consulta
$stmt = $conexion->prepare($sql);

// Reemplaza el ? por el id del usuario
$stmt->bind_param("i", $id_usuario);

// Ejecuta la consulta
$stmt->execute();

// Guarda el resultado
$result = $stmt->get_result();

// Array donde se guardan las reservas
$reservas = [];

// Recorre todas las filas y las guarda en el array
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

// Devuelve las reservas en formato JSON
echo json_encode($reservas);
