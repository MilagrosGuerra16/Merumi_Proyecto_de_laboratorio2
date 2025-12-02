<?php
// JSON y zona horaria
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("America/Argentina/Buenos_Aires");

// Sesión y conexión
session_start();
require "conexion.php";

// Validar sesión
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["success" => false, "error" => "No hay sesión iniciada"]);
    exit;
}

// Leer JSON recibido
$data = json_decode(file_get_contents("php://input"), true);

// Validar datos obligatorios
if (
    !isset($data['id_mesas']) ||
    !is_array($data['id_mesas']) ||
    !isset($data['fecha'], $data['id_turno'], $data['tipo_reserva'])
) {
    echo json_encode(["success" => false, "error" => "Faltan datos"]);
    exit;
}

// Asignar variables
$id_mesas = array_map('intval', $data['id_mesas']);
$fecha = $data['fecha'];
$id_turno = (int)$data['id_turno'];
$tipo_reserva = $data['tipo_reserva'];
$id_usuario = $_SESSION["id_usuario"];
$estado = 2;

// Fecha y hora actual
$hoy = date("Y-m-d");
$ahora_ts = time();

// Bloquear fechas pasadas
if ($fecha < $hoy) {
    echo json_encode(["success" => false, "error" => "No se puede reservar en fechas pasadas"]);
    exit;
}

// Obtener fin del turno
$stmtTurno = $conexion->prepare("
    SELECT fin_hora 
    FROM turnos 
    WHERE id_turnos = ?
");
$stmtTurno->bind_param("i", $id_turno);
$stmtTurno->execute();
$resTurno = $stmtTurno->get_result();

// Validar turno
if ($resTurno->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Turno inválido"]);
    exit;
}

$turno = $resTurno->fetch_assoc();
$fin_turno_ts = strtotime($fecha . " " . $turno['fin_hora']);

// Bloquear turno ya finalizado hoy
if ($fecha === $hoy && $ahora_ts >= $fin_turno_ts) {
    echo json_encode(["success" => false, "error" => "Ese turno ya finalizó"]);
    exit;
}

try {
    // Iniciar transacción
    $conexion->begin_transaction();

    // Verificar que todas las mesas estén libres
    $stmtCheck = $conexion->prepare("
        SELECT id_reserva 
        FROM reserva 
        WHERE id_mesa = ? AND fecha = ? AND id_turno = ?
        FOR UPDATE
    ");

    foreach ($id_mesas as $id_mesa) {
        $stmtCheck->bind_param("isi", $id_mesa, $fecha, $id_turno);
        $stmtCheck->execute();

        if ($stmtCheck->get_result()->num_rows > 0) {
            throw new Exception("La mesa $id_mesa ya está reservada");
        }
    }

    // Insertar reservas
    $stmtInsert = $conexion->prepare("
        INSERT INTO reserva (fecha, id_usuario, id_mesa, id_turno, id_estado)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($id_mesas as $id_mesa) {
        $stmtInsert->bind_param(
            "siiii",
            $fecha,
            $id_usuario,
            $id_mesa,
            $id_turno,
            $estado
        );
        $stmtInsert->execute();
    }

    // Confirmar cambios
    $conexion->commit();
    echo json_encode(["success" => true]);

} catch (Exception $e) {
    // Deshacer todo si falla
    $conexion->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
