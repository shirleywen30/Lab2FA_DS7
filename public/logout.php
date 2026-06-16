<?php
session_start();
session_destroy();  // elimina todos los datos de sesion del servidor, el usuario queda desautenticado

header('Location: login.php'); // redirige al login
exit;