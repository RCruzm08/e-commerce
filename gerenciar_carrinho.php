<?php
session_start();

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$sku   = $_GET['sku']  ?? null;
$acao  = $_GET['acao'] ?? 'add';

if ($sku) {
    if ($acao === 'add' && !in_array($sku, $_SESSION['carrinho'])) {
        $_SESSION['carrinho'][] = $sku;
    } elseif ($acao === 'remove') {
        $pos = array_search($sku, $_SESSION['carrinho']);
        if ($pos !== false) {
            unset($_SESSION['carrinho'][$pos]);
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);
        }
    }
}

header("Location: index.php");
exit;