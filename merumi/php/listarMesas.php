<?php
header('Content-Type: application/json; charset=utf-8');
require "conexion.php";

if (!isset($_GET['fecha']) || !isset($_GET['id_turno'])) {

    $sql = "
    SELECT m.id_mesas, m.nombre, m.capacidad, e.descripcion
    FROM mesas m
    LEFT JOIN estado e ON m.id_estado = e.id_estado
    ORDER BY m.id_mesas
    ";

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

$fecha = $_GET['fecha'];
$id_turno = (int)$_GET['id_turno'];

$hoy = date("Y-m-d");
$ahora = date("H:i:s");

/* ───── obtener fin del turno ───── */
$stmtTurno = $conexion->prepare("
    SELECT fin_hora 
    FROM turnos 
    WHERE id_turnos = ?
");
$stmtTurno->bind_param("i", $id_turno);
$stmtTurno->execute();
$resTurno = $stmtTurno->get_result();

if ($resTurno->num_rows === 0) {
    echo json_encode([]);
    exit;
}

$turno = $resTurno->fetch_assoc();
$fin_turno = $turno['fin_hora'];

/* ───── fecha o turno vencido → todas verdes ───── */
if ($fecha < $hoy || ($fecha === $hoy && $fin_turno <= $ahora)) {

    $sql = "
    SELECT id_mesas, nombre, capacidad 
    FROM mesas 
    ORDER BY id_mesas
    ";

    $res = $conexion->query($sql);

    $mesas = [];
    while ($row = $res->fetch_assoc()) {
        $mesas[] = [
            "id_mesa" => $row["id_mesas"],
            "nombre" => $row["nombre"],
            "capacidad" => $row["capacidad"],
            "estado" => "libre"   // 👈 FORZADO A VERDE
        ];
    }

    echo json_encode($mesas);
    exit;
}

/* ───── fecha válida → estado real ───── */
$sql = "
SELECT 
    m.id_mesas,
    m.nombre,
    m.capacidad,
    CASE 
        WHEN r.id_reserva IS NULL THEN 'libre'
        ELSE 'ocupada'
    END AS estado
FROM mesas m
LEFT JOIN reserva r 
    ON r.id_mesa = m.id_mesas
    AND r.fecha = ?
    AND r.id_turno = ?
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
        "estado" => $row["estado"]
    ];
}

echo json_encode($mesas);
