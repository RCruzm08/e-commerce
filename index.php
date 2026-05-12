<?php
session_start();
require_once "functions.php";

if (!isset($_SESSION['favoritos'])) {
    $_SESSION['favoritos'] = [];
}
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
if (!isset($_SESSION['estoque'])) {
    $_SESSION['estoque'] = [];
}

$produtos = getProdutos();
$busca    = $_GET['busca'] ?? "";
$cat      = $_GET['cat']   ?? "";
$ordem    = $_GET['ordem'] ?? "";

$produtosFiltrados = filtrarEOrdenar($produtos, $busca, $cat, $ordem);

$produtosFavoritos = array_filter($produtos, function($p) {
    return in_array($p['sku'], $_SESSION['favoritos']);
});
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Relampago Maccquin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="style.css" rel="stylesheet"><style>
        .card-esgotado { opacity: 0.6; filter: grayscale(1); }
        .badge-promo   { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">Loja Maccquin Tech 1.0</span>
            <div class="d-flex align-items-center gap-2">

    <button id="toggle-theme" class="btn btn-outline-light btn-sm">
        🌙
    </button>

    <a href="#favoritos" class="btn btn-outline-warning btn-sm">
        ⭐ Meus Favoritos
        <span id="badge-favoritos" class="badge bg-warning text-dark ms-1 <?= empty($_SESSION['favoritos']) ? 'd-none' : '' ?>"><?= count($_SESSION['favoritos']) ?></span>
    </a>

    <a href="#carrinho" class="btn btn-outline-success btn-sm">
        🛒 Carrinho
        <span id="badge-carrinho" class="badge bg-success ms-1 <?= empty($_SESSION['carrinho']) ? 'd-none' : '' ?>"><?= count($_SESSION['carrinho']) ?></span>
    </a>

</div>
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Filtros -->
        <form method="get" class="row g-3 mb-5 p-3 bg-white rounded shadow-sm align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Buscar produto</label>
                <input type="text" name="busca" class="form-control"
                       placeholder="Ex: teclado, monitor..." value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Categoria</label>
                <select name="cat" class="form-select">
                    <option value="">Todas as categorias</option>
                    <option value="Hardware"    <?= $cat === "Hardware"    ? "selected" : "" ?>>Hardware</option>
                    <option value="Monitores"   <?= $cat === "Monitores"   ? "selected" : "" ?>>Monitores</option>
                    <option value="Perifericos" <?= $cat === "Perifericos" ? "selected" : "" ?>>Periféricos</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Ordenar por</label>
                <select name="ordem" class="form-select">
                    <option value="">Padrão</option>
                    <option value="menor_preco" <?= $ordem === "menor_preco" ? "selected" : "" ?>>Menor preço</option>
                    <option value="maior_preco" <?= $ordem === "maior_preco" ? "selected" : "" ?>>Maior preço</option>
                    <option value="az"          <?= $ordem === "az"          ? "selected" : "" ?>>A → Z</option>
                    <option value="za"          <?= $ordem === "za"          ? "selected" : "" ?>>Z → A</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label d-block invisible">.</label>
                <button type="submit" class="btn btn-dark w-100">Filtrar</button>
            </div>
        </form>

        <!-- Listagem de produtos -->
        <div class="row g-4">
            <?php if (empty($produtosFiltrados)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted fs-5">Nenhum produto encontrado.</p>
                </div>
            <?php else: ?>
                <?php foreach ($produtosFiltrados as $p): ?>
                    <?php
                        $isFavorito     = in_array($p['sku'], $_SESSION['favoritos']);
                        $noCarrinho     = in_array($p['sku'], $_SESSION['carrinho']);
                        $estoqueAtual   = $_SESSION['estoque'][$p['sku']] ?? $p['estoque'];
                    ?>
                    <div class="col-md-4">
                        <div class="card h-100 position-relative
                            <?= $estoqueAtual === 0 ? 'card-esgotado' : '' ?>
                            <?= $isFavorito ? 'border-warning' : '' ?>"
                            data-sku="<?= $p['sku'] ?>"
                            data-estoque="<?= $estoqueAtual ?>"
                            data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>"
                            data-categoria="<?= htmlspecialchars($p['categoria'], ENT_QUOTES) ?>"
                            data-preco="<?= $p['preco'] ?>"
                            data-preco-final="<?= $p['preco_final'] ?>">
                            <div class="card-body d-flex flex-column">
                                <?php if ($p['preco_final'] < $p['preco']): ?>
                                    <span class="badge bg-warning text-dark badge-promo">Promoção</span>
                                <?php endif; ?>

                                <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                                <p class="text-muted mb-1"><?= htmlspecialchars($p['categoria']) ?></p>

                                <?php if ($p['preco_final'] < $p['preco']): ?>
                                    <p class="text-decoration-line-through text-muted mb-0">
                                        R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                                    </p>
                                <?php endif; ?>

                                <p class="fw-bold text-success">R$ <?= number_format($p['preco_final'], 2, ',', '.') ?></p>

                                <?php if ($estoqueAtual === 0): ?>
                                    <span class="badge bg-danger badge-estoque">Esgotado</span>
                                <?php else: ?>
                                    <span class="badge bg-success badge-estoque">Em estoque: <?= $estoqueAtual ?></span>
                                    <div class="mt-3 d-flex flex-column gap-2">
                                        <?php if (!$noCarrinho): ?>
                                            <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=add"
                                               class="btn btn-success w-100 btn-carrinho"
                                               data-sku="<?= $p['sku'] ?>"
                                               data-acao="add">🛒 Comprar</a>
                                        <?php else: ?>
                                            <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=remove"
                                               class="btn btn-warning w-100 btn-carrinho"
                                               data-sku="<?= $p['sku'] ?>"
                                               data-acao="remove">✓ No Carrinho — Remover</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <?php if (!$isFavorito): ?>
                                    <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=add"
                                       class="btn btn-outline-warning w-100 btn-favorito"
                                       data-sku="<?= $p['sku'] ?>"
                                       data-acao="add">⭐ Favoritar</a>
                                <?php else: ?>
                                    <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=remove"
                                       class="btn btn-warning w-100 btn-favorito"
                                       data-sku="<?= $p['sku'] ?>"
                                       data-acao="remove">🌟 Remover dos Favoritos</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Meu Carrinho -->
        <section id="carrinho" class="mt-5 pt-4 border-top">
            <h2 class="mb-4">🛒 Meu Carrinho</h2>
            <?php
                $produtosCarrinho = array_filter($produtos, function($p) {
                    return in_array($p['sku'], $_SESSION['carrinho']);
                });
                foreach ($produtosCarrinho as &$p) {
                    $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);
                }
                unset($p);
            ?>
            <p id="carrinho-vazio" class="text-muted <?= !empty($produtosCarrinho) ? 'd-none' : '' ?>">Seu carrinho está vazio.</p>
            <div id="carrinho-grid" class="row g-4">
                <?php foreach ($produtosCarrinho as $p): ?>
                    <div class="col-md-4" id="carrinho-item-<?= $p['sku'] ?>">
                        <div class="card h-100 border-success position-relative">
                            <div class="card-body d-flex flex-column">
                                <?php if ($p['preco_final'] < $p['preco']): ?>
                                    <span class="badge bg-warning text-dark badge-promo">Promoção</span>
                                <?php endif; ?>

                                <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                                <p class="text-muted mb-1"><?= htmlspecialchars($p['categoria']) ?></p>

                                <?php if ($p['preco_final'] < $p['preco']): ?>
                                    <p class="text-decoration-line-through text-muted mb-0">
                                        R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                                    </p>
                                <?php endif; ?>

                                <p class="fw-bold text-success">R$ <?= number_format($p['preco_final'], 2, ',', '.') ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=remove"
                                   class="btn btn-outline-danger w-100 btn-carrinho"
                                   data-sku="<?= $p['sku'] ?>"
                                   data-acao="remove"
                                   data-contexto="carrinho">🗑 Remover do Carrinho</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php $total = empty($produtosCarrinho) ? 0 : array_sum(array_column(array_values($produtosCarrinho), 'preco_final')); ?>
            <div id="carrinho-total" class="mt-4 p-3 bg-white rounded shadow-sm d-flex justify-content-between align-items-center <?= empty($produtosCarrinho) ? 'd-none' : '' ?>">
                <span class="fs-5 fw-semibold">Total:</span>
                <span id="carrinho-total-valor" class="fs-4 fw-bold text-success">R$ <?= number_format($total, 2, ',', '.') ?></span>
            </div>
        </section>

        <!-- Meus Favoritos -->
        <section id="favoritos" class="mt-5 pt-4 border-top">
            <h2 class="mb-4">⭐ Meus Favoritos</h2>
            <?php if (empty($produtosFavoritos)): ?>
                <p class="text-muted">Sua lista de desejos está vazia.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($produtosFavoritos as &$p):
                        $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);
                    ?>
                        <div class="col-md-4">
                            <div class="card h-100 border-warning position-relative <?= $p['estoque'] === 0 ? 'card-esgotado' : '' ?>">
                                <div class="card-body d-flex flex-column">
                                    <?php if ($p['preco_final'] < $p['preco']): ?>
                                        <span class="badge bg-warning text-dark badge-promo">Promoção</span>
                                    <?php endif; ?>

                                    <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                                    <p class="text-muted mb-1"><?= htmlspecialchars($p['categoria']) ?></p>

                                    <?php if ($p['preco_final'] < $p['preco']): ?>
                                        <p class="text-decoration-line-through text-muted mb-0">
                                            R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                                        </p>
                                    <?php endif; ?>

                                    <p class="fw-bold text-success">R$ <?= number_format($p['preco_final'], 2, ',', '.') ?></p>

                                    <?php if ($p['estoque'] === 0): ?>
                                        <span class="badge bg-danger">Esgotado</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Em estoque: <?= $p['estoque'] ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer">
                                    <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=remove"
                                       class="btn btn-warning w-100">🌟 Remover dos Favoritos</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; unset($p); ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script src="script.js"></script>
</body>
</html>
