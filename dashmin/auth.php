<?php
session_start();

/* =====================================================
   VERIFICAR SESSÃO
===================================================== */

if (
    empty($_SESSION['auth']) ||
    empty($_SESSION['user_id'])
) {
    header("Location: login.php");
    exit;
}

/* =====================================================
   PROTEÇÃO EXTRA (ANTI-HIJACK)
===================================================== */

if (
    $_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['agent'] !== $_SERVER['HTTP_USER_AGENT']
) {
    session_destroy();
    header("Location: login.php");
    exit;
}