<?php
// devolver respuesta en JSON
header('Content-Type: application/json; charset=utf-8');

session_start();
include "conexion.php";

// validar sesion
if (!isset($_SESSION["id_usuario"])) {
    echo json_encode([
        "success" => false,
        "error" => "No hay sesión iniciada"
    ]);
    exit;
}

// leer datos JSON
$data = json_decode(file_get_contents("php://input"), true);

// validar datos recibidos
if (!isset($data['id_mesa'], $data['fecha'], $data['id_turno'], $data['tipo_reserva'])) {
    echo json_encode([
        "success" => false,
        "error" => "Faltan datos"
    ]);
    exit;
}

$id_mesa      = (int)$data['id_mesa'];
$fecha        = $data['fecha'];
$id_turno     = (int)$data['id_turno'];
$tipo_reserva = $data['tipo_reserva'];
$id_usuario   = $_SESSION["id_usuario"];
$estado       = 2;

$hoy   = date("Y-m-d");
$ahora = date("H:i:s");

// validar fecha
if ($fecha < $hoy) {
    echo json_encode([
        "success" => false,
        "error" => "No se puede reservar en fechas pasadas"
    ]);
    exit;
}

// obtener fin del turno
$stmtTurno = $conexion->prepare("
    SELECT fin_hora 
    FROM turnos 
    WHERE id_turnos = ?
");
$stmtTurno->bind_param("i", $id_turno);
$stmtTurno->execute();
$resTurno = $stmtTurno->get_result();

// validar turno
if ($resTurno->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "error" => "Turno inválido"
    ]);
    exit;
}

$turno     = $resTurno->fetch_assoc();
$fin_turno = $turno['fin_hora'];

// validar turno vencido
if ($fecha === $hoy && $fin_turno <= $ahora) {
    echo json_encode([
        "success" => false,
        "error" => "Ese turno ya finalizó"
    ]);
    exit;
}

try {
    // iniciar transaccion
    $conexion->begin_transaction();

    // verificar mesa disponible
    $stmtCheck = $conexion->prepare("
        SELECT id_reserva 
        FROM reserva 
        WHERE id_mesa = ? AND fecha = ? AND id_turno = ?
        FOR UPDATE
    ");
    $stmtCheck->bind_param("isi", $id_mesa, $fecha, $id_turno);
    $stmtCheck->execute();

    if ($stmtCheck->get_result()->num_rows > 0) {
        throw new Exception("La mesa ya está reservada");
    }

    // reserva desde admin o cliente externo
    if ($tipo_reserva === "admin") {

        $stmt = $conexion->prepare("
            INSERT INTO reserva (fecha, id_usuario, id_mesa, id_turno, id_estado)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siiii",
            $fecha, $id_usuario, $id_mesa, $id_turno, $estado
        );

    } else {

        $nombre = $data['nombre_externo'] ?? null;
        $tel    = $data['telefono_externo'] ?? null;

        if (!$nombre || !$tel) {
            throw new Exception("Datos del cliente externo incompletos");
        }

        $stmt = $conexion->prepare("
            INSERT INTO reserva 
            (fecha, id_mesa, id_turno, id_estado, nombre_externo, telefono_externo)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "siisss",
            $fecha, $id_mesa, $id_turno, $estado, $nombre, $tel
        );
    }

    // ejecutar y confirmar
    $stmt->execute();
    $conexion->commit();

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    // error y rollback
    $conexion->rollback();
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
