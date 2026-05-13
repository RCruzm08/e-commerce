<?php
session_start();
require_once "functions.php";

// Só aceita requisições POST para evitar manipulação via URL
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin.php");
    exit;
}

$sku        = sanitizarString($_POST['sku']   ?? "");
$acao       = sanitizarString($_POST['acao']  ?? "");
$quantidade = (int) ($_POST['quantidade']     ?? 0);

// Valida se a ação enviada é uma das permitidas para estoque
if (!validarAcao($acao, ['add', 'sub', 'set'])) {
    $_SESSION['estoque_feedback'] = ['tipo' => 'erro', 'msg' => 'Ação inválida.'];
    header("Location: admin.php");
    exit;
}

// Valida quantidade: deve ser positiva (exceto 'sub' que pode ser 0 se zerar)
if ($quantidade < 0 || ($acao !== 'set' && $quantidade === 0)) {
    $_SESSION['estoque_feedback'] = ['tipo' => 'erro', 'msg' => 'Quantidade inválida.'];
    header("Location: admin.php");
    exit;
}

$produtos = getProdutos();
$indice   = null;

// Busca o índice do produto no array para edição direta
foreach ($produtos as $i => $p) {
    if ($p['sku'] === $sku) {
        $indice = $i;
        break;
    }
}

if ($indice === null) {
    $_SESSION['estoque_feedback'] = ['tipo' => 'erro', 'msg' => "SKU \"$sku\" não encontrado."];
    header("Location: admin.php");
    exit;
}

$estoqueAtual = $produtos[$indice]['estoque'];
$nomeProduto  = $produtos[$indice]['nome'];

// Aplica a operação de estoque conforme a ação recebida
$novoEstoque = match($acao) {
    'add' => $estoqueAtual + $quantidade,
    'sub' => max(0, $estoqueAtual - $quantidade),
    'set' => $quantidade,
};

$produtos[$indice]['estoque'] = $novoEstoque;

// Persiste o catálogo atualizado de volta no JSON
if (salvarProdutos($produtos)) {

    // Sincroniza o estoque da sessão com o novo valor do JSON
    $_SESSION['estoque'][$sku] = $novoEstoque;

    $operacao = match($acao) {
        'add' => "+$quantidade unidades",
        'sub' => "-$quantidade unidades",
        'set' => "definido para $novoEstoque",
    };

    $_SESSION['estoque_feedback'] = [
        'tipo' => 'sucesso',
        'msg'  => "\"$nomeProduto\" — estoque $operacao. Total atual: $novoEstoque."
    ];

} else {
    $_SESSION['estoque_feedback'] = ['tipo' => 'erro', 'msg' => 'Falha ao salvar o arquivo JSON. Verifique as permissões.'];
}

header("Location: admin.php");
exit;