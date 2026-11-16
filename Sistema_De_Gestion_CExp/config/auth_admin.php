<?php
require_once __DIR__ . '/auth.php';


if (!isset($_SESSION['id_grupo']) || (int)$_SESSION['id_grupo'] !== 1) {
    header("Location: ../views/reportes.php");
    exit();
}
