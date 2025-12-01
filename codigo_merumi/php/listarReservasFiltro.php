<?php
// php/listarReservas.php
header("Content-Type: application/json");
require "conexion.php"; 

// Recibir parámetros de consulta (GET)
$filtro = $_GET["filtro"] ?? "hoy";
$fecha = $_GET["fecha"] ?? null;
$mes = $_GET["mes"] ?? null;
$desde = $_GET["desde"] ?? null;
$hasta = $_GET["hasta"] ?? null;

// Consulta base
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

// Aplicar filtros
$where = [];

if ($filtro === "hoy") {
    // Hoy
    $where[] = "r.fecha = CURDATE()";
} elseif ($filtro === "fecha" && $fecha) {
    // Fecha específica 
    $fecha = $conexion->real_escape_string($fecha);
    $where[] = "r.fecha = '$fecha'";
} elseif ($filtro === "mes") {
    // Todo el mes actual
    $where[] = "MONTH(r.fecha) = MONTH(CURDATE()) AND YEAR(r.fecha) = YEAR(CURDATE())";
} elseif ($filtro === "mes_especifico" && $mes) {
    // Mes específico 
    $mes = $conexion->real_escape_string($mes); 
    $where[] = "DATE_FORMAT(r.fecha, '%Y-%m') = '$mes'";
} elseif ($filtro === "rango" && $desde && $hasta) {
    // Rango de fechas 
    $desde = $conexion->real_escape_string($desde);
    $hasta = $conexion->real_escape_string($hasta);
    
    $where[] = "r.fecha BETWEEN '$desde' AND '$hasta'";
}

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Ordenar por fecha y turno 
$sql .= " ORDER BY r.fecha ASC, t.nombre ASC";

// Ejecutar consulta
$result = $conexion->query($sql);
$reservas = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Nombre final para mostrar en JS
        $row["nombre_final"] = $row["nombre_usuario"] ?: $row["nombre_externo"];
        $reservas[] = $row;
    }
}

echo json_encode($reservas);

// Cierra la conexión
$conexion->close();
?>