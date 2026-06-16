<?php
session_start();
header('Content-Type: application/json'); // indica que la respuesta es JSON y no HTML

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Sanitizador.php';

$db  = new Database();
$pdo = $db->conectar();


$campo = $_GET['campo'] ?? '';
$valor = trim($_GET['valor'] ?? '');

switch ($campo) {
    case 'usuario':
        $valor   = Sanitizador::usuario($valor);
        $columna = 'Usuario';
        $error   = 'El usuario ya está en uso.';
        break;
    case 'correo':
        $valor   = Sanitizador::email($valor);
        $columna = 'Correo';
        $error   = 'El correo ya está registrado.';
        break;
    default:
        echo json_encode(true);
        exit;
}

if ($valor === '') {
    echo json_encode(true);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE {$columna} = :valor LIMIT 1");
$stmt->execute([':valor' => $valor]);

// JQuery Validate interpreta:
// true  → campo disponible, formulario valido
// string → campo no disponible, muestra el string como mensaje de error
echo json_encode($stmt->fetch() === false ? true : $error);
