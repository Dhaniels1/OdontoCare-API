<?php

session_start();
require_once __DIR__ . '/config/seguranca.php';
validarPostCsrf();

$_SESSION = [];
session_destroy();

header("location:login.php");
exit;
