<?php
session_start();
header("Content-Type: application/json");
require "conexion.php";

if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([]);
    exit;
}

$id_usuario = $_SESSION["id_usuario"];

$sql = "
SELECT 
    r.id_reserva,
    r.id_mesa,
    m.nombre AS nombre_mesa,
    r.fecha,
    t.nombre AS nombre_turno,
    e.descripcion AS estado
FROM reserva r
INNER JOIN mesas m ON r.id_mesa = m.id_mesas
INNER JOIN turnos t ON r.id_turno = t.id_turnos
INNER JOIN estado e ON r.id_estado = e.id_estado
WHERE r.id_usuario = ?
AND (
    r.fecha > CURDATE()
    OR (r.fecha = CURDATE() AND t.fin_hora > CURTIME())
)
ORDER BY r.fecha ASC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

echo json_encode($reservas);
