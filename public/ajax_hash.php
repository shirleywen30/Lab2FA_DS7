<?php
session_start();

// verifica que el usuario haya iniciado sesion completa (contraseña y codigo 2fa)
// si alguien intenta enrar a esta url directamente sin estar autenticado se le bloquea
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SI') {
    http_response_code(403); // le dice al navegador "acceso denegado"
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../classes/CSRF.php';

header('Content-Type: application/json; charset=utf-8');

// solo debe reciber datos enviados por formulario (POST)
// si alguien intenta acceder la url en el navegador (GET), se rechaza
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// verifica que la peticion venga desde nuestra propia pagina y no desde un sitio externo malicioso
if (!CSRF::validarToken($_POST['csrf_token'] ?? null)) {
    echo json_encode(['error' => 'Token CSRF inválido']);
    exit;
}

$accion   = $_POST['accion']   ?? ''; // que quiere hacer el usuario: generar o validar
$password = $_POST['password'] ?? '';

if ($accion === 'generar') {
    if ($password === '') {
        echo json_encode(['error' => 'Ingresa una contraseña']);
        exit;
    }
    // convierte la contraseña en un hash bcrypt
    // cost 13 para que el calculo sea lento y si alguien intenta robar la bd le tome mucho tiempo en adivinar la contraseña
    echo json_encode(['hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 13])]);
    exit;
}

if ($accion === 'validar') {
    $hashIngresado = $_POST['hash_ingresado'] ?? '';
    // compara la contraseña en texto plano con el hash guardado
    if ($password === '' || $hashIngresado === '') {
        echo json_encode(['error' => 'Completa ambos campos']);
        exit;
    }
    // password_verify compara la contraseña en texo plano con el hash almacenado
    // no desencripta el hash, vuelve a calcular el hash de la contraseña y los compara
    echo json_encode(['coincide' => password_verify($password, $hashIngresado)]);
    exit;
}

echo json_encode(['error' => 'Acción desconocida']);
