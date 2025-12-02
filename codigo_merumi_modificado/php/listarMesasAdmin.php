<?php
// Devuelve mesas con estado según fecha y turno (admin)

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("America/Argentina/Buenos_Aires");

require "conexion.php";

// Si no hay fecha o turno, devuelve todas libres
if (!isset($_GET['fecha']) || !isset($_GET['id_turno'])) {

    $sql = "SELECT id_mesas, nombre, capacidad FROM mesas ORDER BY id_mesas";
    $res = $conexion->query($sql);

    $mesas = [];
    while ($row = $res->fetch_assoc()) {
        $mesas[] = [
            "id_mesa" => $row["id_mesas"],
            "nombre" => $row["nombre"],
            "capacidad" => $row["capacidad"],
            "estado" => "libre"
        ];
    }

    echo json_encode($mesas);
    exit;
}

$fecha    = $_GET['fecha'];
$id_turno = (int)$id_turno = $_GET['id_turno'];

$hoy = date("Y-m-d");
$ahora_ts = time();

// Obtener hora de fin del turno
$stmtTurno = $conexion->prepare("
    SELECT fin_hora 
    FROM turnos 
    WHERE id_turnos = ?
");
$stmtTurno->bind_param("i", $id_turno);
$stmtTurno->execute();
$resTurno = $stmtTurno->get_result();

// Si no existe el turno, devuelve vacío
if ($resTurno->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$turno = $resTurno->fetch_assoc();
$fin_turno_ts = strtotime($fecha . " " . $turno['fin_hora']);

// Si la fecha pasó o el turno terminó, todas libres
if ($fecha < $hoy || ($fecha === $hoy && $ahora_ts >= $fin_turno_ts)) {

    $sql = "SELECT id_mesas, nombre, capacidad FROM mesas ORDER BY id_mesas";
    $res = $conexion->query($sql);

    $mesas = [];
    while ($row = $res->fetch_assoc()) {
        $mesas[] = [
            "id_mesa" => $row["id_mesas"],
            "nombre" => $row["nombre"],
            "capacidad" => $row["capacidad"],
            "estado" => "libre"
        ];
    }

    echo json_encode($mesas);
    exit;
}

// Consultar mesas con reservas del turno
$sql = "
SELECT 
    m.id_mesas,
    m.nombre,
    m.capacidad,
    CASE 
        WHEN r.id_reserva IS NULL THEN 'libre'
        ELSE 'reservada'
    END AS estado,
    u.nombre AS nombre_usuario,
    r.nombre_externo,
    r.telefono_externo
FROM mesas m
LEFT JOIN reserva r 
    ON r.id_mesa = m.id_mesas
    AND r.fecha = ?
    AND r.id_turno = ?
LEFT JOIN usuario u 
    ON u.id_usuario = r.id_usuario
ORDER BY m.id_mesas
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $fecha, $id_turno);
$stmt->execute();
$res = $stmt->get_result();

$mesas = [];
while ($row = $res->fetch_assoc()) {
    $mesas[] = [
        "id_mesa" => $row["id_mesas"],
        "nombre" => $row["nombre"],
        "capacidad" => $row["capacidad"],
        "estado" => $row["estado"],
        "nombre_reserva" => $row["nombre_usuario"] ?: $row["nombre_externo"],
        "telefono" => $row["telefono_externo"]
    ];
}

echo json_encode($mesas);
