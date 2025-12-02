<?php

// Devuelve la respuesta en formato JSON
header("Content-Type: application/json");

// Conexión a la base de datos
require "conexion.php";

// Parámetros recibidos por GET para filtrar reservas
$filtro = $_GET["filtro"] ?? "hoy";
$fecha  = $_GET["fecha"]  ?? null;
$mes    = $_GET["mes"]    ?? null;
$desde  = $_GET["desde"]  ?? null;
$hasta  = $_GET["hasta"]  ?? null;

// Consulta base con JOIN para traer datos completos de la reserva
$sql = "
SELECT 
    r.id_reserva,
    r.fecha,
    r.id_usuario,
    r.id_mesa,
    r.id_turno,
    r.id_estado,
    r.nombre_externo,
    r.telefono_externo,
    m.nombre AS nombre_mesa,
    t.nombre AS nombre_turno,
    u.nombre AS nombre_usuario,
    u.id_tipo_persona
FROM reserva r
LEFT JOIN mesas m ON r.id_mesa = m.id_mesas
LEFT JOIN turnos t ON r.id_turno = t.id_turnos
LEFT JOIN usuario u ON r.id_usuario = u.id_usuario
";

// Array para condiciones WHERE
$where = [];

// Filtro por tipo
if ($filtro === "hoy") {
    $where[] = "r.fecha = CURDATE()";
} elseif ($filtro === "fecha" && $fecha) {
    $fecha = $conexion->real_escape_string($fecha);
    $where[] = "r.fecha = '$fecha'";
} elseif ($filtro === "mes") {
    $where[] = "MONTH(r.fecha) = MONTH(CURDATE()) AND YEAR(r.fecha) = YEAR(CURDATE())";
} elseif ($filtro === "mes_especifico" && $mes) {
    $mes = $conexion->real_escape_string($mes);
    $where[] = "DATE_FORMAT(r.fecha, '%Y-%m') = '$mes'";
} elseif ($filtro === "rango" && $desde && $hasta) {
    $desde = $conexion->real_escape_string($desde);
    $hasta = $conexion->real_escape_string($hasta);
    $where[] = "r.fecha BETWEEN '$desde' AND '$hasta'";
}

// Agrega el WHERE si hay filtros
if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Ordena por fecha y turno
$sql .= " ORDER BY r.fecha ASC, t.nombre ASC";

// Ejecuta la consulta
$result = $conexion->query($sql);
$reservas = [];

// Recorre resultados
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Prioriza nombre del usuario registrado, si no usa externo
        $row["nombre_final"] = $row["nombre_usuario"] ?: $row["nombre_externo"];
        $reservas[] = $row;
    }
}

// Devuelve las reservas en JSON
echo json_encode($reservas);

// Cierra conexión
$conexion->close();

?>
