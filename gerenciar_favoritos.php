<?php
// Sistema de favoritos(adiciona em favorito)
session_start();

if (!isset($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}

$sku = $_GET['sku'] ?? null;
$acao = $_GET['acao'] ?? 'add';

if ($sku) {
    if ($acao === 'add' && !in_array($sku, $_SESSION['favoritos'])) {
        $_SESSION['favoritos'][] = $sku;
    } elseif ($acao === 'remove') {
        $posicao = array_search($sku, $_SESSION['favoritos']);
        if ($posicao !== false) {
            unset($_SESSION['favoritos'][$posicao]);
        }
    }
}

header("Location: index.php");
exit;
