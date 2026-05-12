<?php
session_start();
require_once "functions.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['estoque'])) {
    $_SESSION['estoque'] = [];
}

$sku  = $_GET['sku']  ?? null;
$acao = $_GET['acao'] ?? 'add';

if ($sku) {
    $produtos = getProdutos();
    $produto  = null;
    foreach ($produtos as $p) {
        if ($p['sku'] === $sku) {
            $produto = $p;
            break;
        }
    }

    if ($acao === 'add' && $produto && !in_array($sku, $_SESSION['carrinho'])) {
        $estoqueAtual = $_SESSION['estoque'][$sku] ?? $produto['estoque'];
        if ($estoqueAtual > 0) {
            $_SESSION['carrinho'][] = $sku;
            $_SESSION['estoque'][$sku] = $estoqueAtual - 1;
        }
    } elseif ($acao === 'remove') {
        $pos = array_search($sku, $_SESSION['carrinho']);
        if ($pos !== false) {
            unset($_SESSION['carrinho'][$pos]);
            $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);

            $estoqueAtual = $_SESSION['estoque'][$sku] ?? ($produto['estoque'] - 1);
            $_SESSION['estoque'][$sku] = $estoqueAtual + 1;
        }
    }
}

header("Location: index.php");
exit;
