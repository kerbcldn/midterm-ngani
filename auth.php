<?php
session_start();

function require_admin() {
    if (empty($_SESSION["admin_id"])) {
        header("Location: login.php");
        exit;
    }
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>