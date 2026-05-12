<?php
session_start();
require_once "functions.php";

if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

if (!isset($_SESSION['estoque'])) {
    $_SESSION['estoque'] = [];
}

$sku = $_GET['sku'] ?? null;
$acao = $_GET['acao'] ?? 'add';

if ($sku) {

    $produtos = getProdutos();
    $produto = null;

    foreach ($produtos as $p) {
        if ($p['sku'] === $sku) {
            $produto = $p;
            break;
        }
    }

    if ($produto) {

        if (!isset($_SESSION['estoque'][$sku])) {
            $_SESSION['estoque'][$sku] = $produto['estoque'];
        }

     
        if ($acao === 'add') {

            if ($_SESSION['estoque'][$sku] > 0) {

                $_SESSION['carrinho'][] = $sku;

                $_SESSION['estoque'][$sku]--;
            }
        }

      
        if ($acao === 'remove') {

            $key = array_search($sku, $_SESSION['carrinho']);

            if ($key !== false) {

                unset($_SESSION['carrinho'][$key]);

                $_SESSION['carrinho'] = array_values($_SESSION['carrinho']);

                $_SESSION['estoque'][$sku]++;

                if ($_SESSION['estoque'][$sku] > $produto['estoque']) {
                    $_SESSION['estoque'][$sku] = $produto['estoque'];
                }
            }
        }
    }
}

header("Location: index.php");
exit;
?>
