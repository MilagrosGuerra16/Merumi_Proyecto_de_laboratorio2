<?php
// Respuesta en JSON y zona horaria
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("America/Argentina/Buenos_Aires");

// Inicia sesión y conexión
session_start();
require "conexion.php";

// Verifica sesión iniciada
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode(["success" => false, "error" => "No hay sesión iniciada"]);
    exit;
}

// Lee datos enviados en JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verifica datos obligatorios
if (!isset($data['id_mesa'], $data['fecha'], $data['id_turno'], $data['tipo_reserva'])) {
    echo json_encode(["success" => false, "error" => "Faltan datos"]);
    exit;
}

// Asigna variables
$id_mesa = (int)$data['id_mesa'];
$fecha = $data['fecha'];
$id_turno = (int)$data['id_turno'];
$tipo_reserva = $data['tipo_reserva'];
$id_usuario = $_SESSION["id_usuario"];
$estado = 2;

// Fecha y hora actual
$hoy = date("Y-m-d");
$ahora_ts = time();

// Bloquea fechas pasadas
if ($fecha < $hoy) {
    echo json_encode(["success" => false, "error" => "No se puede reservar en fechas pasadas"]);
    exit;
}

// Obtiene fin del turno
$stmtTurno = $conexion->prepare("
    SELECT fin_hora 
    FROM turnos 
    WHERE id_turnos = ?
");
$stmtTurno->bind_param("i", $id_turno);
$stmtTurno->execute();
$resTurno = $stmtTurno->get_result();

// Turno inválido
if ($resTurno->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "Turno inválido"]);
    exit;
}

$turno = $resTurno->fetch_assoc();
$fin_turno_ts = strtotime($fecha . " " . $turno['fin_hora']);

// Bloquea turno vencido
if ($fecha === $hoy && $ahora_ts >= $fin_turno_ts) {
    echo json_encode(["success" => false, "error" => "Ese turno ya finalizó"]);
    exit;
}

try {
    // Inicia transacción
    $conexion->begin_transaction();

    // Verifica si la mesa ya está reservada
    $stmtCheck = $conexion->prepare("
        SELECT id_reserva 
        FROM reserva 
        WHERE id_mesa = ? AND fecha = ? AND id_turno = ?
        FOR UPDATE
    ");
    $stmtCheck->bind_param("isi", $id_mesa, $fecha, $id_turno);
    $stmtCheck->execute();

    if ($stmtCheck->get_result()->num_rows > 0) {
        throw new Exception("La mesa ya está reservada en ese turno");
    }

    // Reserva hecha por admin
    if ($tipo_reserva === "admin") {
        $stmt = $conexion->prepare("
            INSERT INTO reserva (fecha, id_usuario, id_mesa, id_turno, id_estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("siiii", $fecha, $id_usuario, $id_mesa, $id_turno, $estado);
    } 
    // Reserva externa
    else {
        $nombre = $data['nombre_externo'] ?? null;
        $tel = $data['telefono_externo'] ?? null;

        if (!$nombre || !$tel) {
            throw new Exception("Datos del cliente externo incompletos");
        }

        $stmt = $conexion->prepare("
            INSERT INTO reserva (fecha, id_mesa, id_turno, id_estado, nombre_externo, telefono_externo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("siisss", $fecha, $id_mesa, $id_turno, $estado, $nombre, $tel);
    }

    // Ejecuta y confirma
    $stmt->execute();
    $conexion->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    // Revierte ante error
    $conexion->rollback();
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
