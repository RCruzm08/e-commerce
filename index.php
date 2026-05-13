<?php
session_start();
require_once "functions.php";

if (!isset($_SESSION['favoritos'])) $_SESSION['favoritos'] = [];
if (!isset($_SESSION['carrinho']))  $_SESSION['carrinho']  = [];
if (!isset($_SESSION['estoque']))   $_SESSION['estoque']   = [];

$produtos = getProdutos();
$busca    = $_GET['busca'] ?? "";
$cat      = $_GET['cat']   ?? "";
$ordem    = $_GET['ordem'] ?? "";

$produtosFiltrados = filtrarEOrdenar($produtos, $busca, $cat, $ordem);

$produtosFavoritos = array_filter($produtos, fn($p) => in_array($p['sku'], $_SESSION['favoritos']));

$produtosCarrinho = array_filter($produtos, fn($p) => in_array($p['sku'], $_SESSION['carrinho']));
foreach ($produtosCarrinho as &$p) {
    $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);
}
unset($p);

$total = empty($produtosCarrinho) ? 0 : array_sum(array_column(array_values($produtosCarrinho), 'preco_final'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maccquin Tech</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- ── Navbar ──────────────────────────────────────────── -->
    <nav class="navbar">
        <div class="container">
            <span class="navbar-brand">Maccquin Tech</span>

            <div class="navbar-actions">
           <a href="admin.php" class="btn btn-ghost btn-sm">⚙️ Admin</a>
                <button id="toggle-theme" class="btn btn-ghost btn-sm">🌙</button>

                <a href="#favoritos" class="btn btn-outline-warning btn-sm">
                    ⭐ Favoritos
                    <span id="badge-favoritos"
                          class="badge-nav gold <?= empty($_SESSION['favoritos']) ? 'd-none' : '' ?>">
                        <?= count($_SESSION['favoritos']) ?>
                    </span>
                </a>

                <a href="#carrinho" class="btn btn-outline-success btn-sm">
                    🛒 Carrinho
                    <span id="badge-carrinho"
                          class="badge-nav accent <?= empty($_SESSION['carrinho']) ? 'd-none' : '' ?>">
                        <?= count($_SESSION['carrinho']) ?>
                    </span>
                </a>

            </div>
        </div>
    </nav>

    <div class="container">

        <!-- ── Filtros ──────────────────────────────────────── -->
        <form method="get" class="filter-form">

            <div class="field-group">
                <label class="form-label">Buscar produto</label>
                <input type="text" name="busca" class="form-control"
                       placeholder="Ex: teclado, monitor, RTX..."
                       value="<?= htmlspecialchars($busca) ?>">
            </div>

            <div class="field-group">
                <label class="form-label">Categoria</label>
                <select name="cat" class="form-select">
                    <option value="">Todas</option>
                    <option value="Hardware"    <?= $cat === "Hardware"    ? "selected" : "" ?>>Hardware</option>
                    <option value="Monitores"   <?= $cat === "Monitores"   ? "selected" : "" ?>>Monitores</option>
                    <option value="Perifericos" <?= $cat === "Perifericos" ? "selected" : "" ?>>Periféricos</option>
                </select>
            </div>

            <div class="field-group">
                <label class="form-label">Ordenar por</label>
                <select name="ordem" class="form-select">
                    <option value="">Padrão</option>
                    <option value="menor_preco" <?= $ordem === "menor_preco" ? "selected" : "" ?>>Menor preço</option>
                    <option value="maior_preco" <?= $ordem === "maior_preco" ? "selected" : "" ?>>Maior preço</option>
                    <option value="az"          <?= $ordem === "az"          ? "selected" : "" ?>>A → Z</option>
                    <option value="za"          <?= $ordem === "za"          ? "selected" : "" ?>>Z → A</option>
                </select>
            </div>

            <div class="field-group">
                <label class="form-label" style="visibility:hidden">.</label>
                <button type="submit" class="btn btn-submit btn-w-full">Filtrar</button>
            </div>

        </form>

        <!-- ── Produtos ──────────────────────────────────────── -->
        <div class="product-grid">
            <?php if (empty($produtosFiltrados)): ?>
                <p class="empty-state" style="grid-column:1/-1">Nenhum produto encontrado.</p>
            <?php else: ?>
                <?php foreach ($produtosFiltrados as $p):
                    $isFavorito   = in_array($p['sku'], $_SESSION['favoritos']);
                    $noCarrinho   = in_array($p['sku'], $_SESSION['carrinho']);
                    $estoqueAtual = $_SESSION['estoque'][$p['sku']] ?? $p['estoque'];
                ?>
                <div class="card
                    <?= $estoqueAtual === 0 ? 'card-esgotado' : '' ?>
                    <?= $isFavorito ? 'border-warning' : '' ?>"
                    data-sku="<?= $p['sku'] ?>"
                    data-estoque="<?= $estoqueAtual ?>"
                    data-nome="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>"
                    data-categoria="<?= htmlspecialchars($p['categoria'], ENT_QUOTES) ?>"
                    data-preco="<?= $p['preco'] ?>"
                    data-preco-final="<?= $p['preco_final'] ?>">

                    <div class="card-body">

                        <?php if ($p['preco_final'] < $p['preco']): ?>
                            <span class="badge-promo">Promoção</span>
                        <?php endif; ?>

                        <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                        <p class="card-categoria"><?= htmlspecialchars($p['categoria']) ?></p>

                        <?php if ($p['preco_final'] < $p['preco']): ?>
                            <p class="card-preco-original">
                                R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </p>
                        <?php endif; ?>

                        <p class="card-preco-final">
                            R$ <?= number_format($p['preco_final'], 2, ',', '.') ?>
                        </p>

                        <?php if ($estoqueAtual === 0): ?>
                            <span class="badge-estoque bg-danger">● Esgotado</span>
                        <?php else: ?>
                            <span class="badge-estoque bg-success">● Em estoque: <?= $estoqueAtual ?></span>
                            <div class="card-btn-group">
                                <?php if (!$noCarrinho): ?>
                                    <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=add"
                                       class="btn btn-success btn-w-full btn-carrinho"
                                       data-sku="<?= $p['sku'] ?>"
                                       data-acao="add">🛒 Comprar</a>
                                <?php else: ?>
                                    <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=remove"
                                       class="btn btn-warning btn-w-full btn-carrinho"
                                       data-sku="<?= $p['sku'] ?>"
                                       data-acao="remove">✓ No Carrinho — Remover</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer">
                        <?php if (!$isFavorito): ?>
                            <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=add"
                               class="btn btn-outline-warning btn-w-full btn-favorito"
                               data-sku="<?= $p['sku'] ?>"
                               data-acao="add">⭐ Favoritar</a>
                        <?php else: ?>
                            <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=remove"
                               class="btn btn-warning btn-w-full btn-favorito"
                               data-sku="<?= $p['sku'] ?>"
                               data-acao="remove">🌟 Remover dos Favoritos</a>
                        <?php endif; ?>
                    </div>

                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── Carrinho ──────────────────────────────────────── -->
        <section id="carrinho">
            <div class="section-header">
                <h2>🛒 Meu Carrinho</h2>
                <?php if (!empty($produtosCarrinho)): ?>
                    <span class="section-count"><?= count($produtosCarrinho) ?> iten<?= count($produtosCarrinho) > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <p id="carrinho-vazio" class="empty-state <?= !empty($produtosCarrinho) ? 'd-none' : '' ?>">
                Seu carrinho está vazio.
            </p>

            <div id="carrinho-grid" class="product-grid">
                <?php foreach ($produtosCarrinho as $p): ?>
                <div class="card border-success" id="carrinho-item-<?= $p['sku'] ?>"
                     data-preco-final="<?= $p['preco_final'] ?>">

                    <div class="card-body">

                        <?php if ($p['preco_final'] < $p['preco']): ?>
                            <span class="badge-promo">Promoção</span>
                        <?php endif; ?>

                        <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                        <p class="card-categoria"><?= htmlspecialchars($p['categoria']) ?></p>

                        <?php if ($p['preco_final'] < $p['preco']): ?>
                            <p class="card-preco-original">
                                R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                            </p>
                        <?php endif; ?>

                        <p class="card-preco-final">
                            R$ <?= number_format($p['preco_final'], 2, ',', '.') ?>
                        </p>

                    </div>

                    <div class="card-footer">
                        <a href="gerenciar_carrinho.php?sku=<?= $p['sku'] ?>&acao=remove"
                           class="btn btn-outline-danger btn-w-full btn-carrinho"
                           data-sku="<?= $p['sku'] ?>"
                           data-acao="remove"
                           data-contexto="carrinho">🗑 Remover do Carrinho</a>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

            <div id="carrinho-total" class="<?= empty($produtosCarrinho) ? 'd-none' : '' ?>">
                <span class="carrinho-total-label">Total</span>
                <span id="carrinho-total-valor">
                    R$ <?= number_format($total, 2, ',', '.') ?>
                </span>
            </div>
        </section>

        <!-- ── Favoritos ─────────────────────────────────────── -->
        <section id="favoritos">
            <div class="section-header">
                <h2>⭐ Meus Favoritos</h2>
                <?php if (!empty($produtosFavoritos)): ?>
                    <span class="section-count"><?= count($produtosFavoritos) ?> iten<?= count($produtosFavoritos) > 1 ? 's' : '' ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($produtosFavoritos)): ?>
                <p class="empty-state">Sua lista de desejos está vazia.</p>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($produtosFavoritos as &$p):
                        $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);
                    ?>
                    <div class="card border-warning
                        <?= $p['estoque'] === 0 ? 'card-esgotado' : '' ?>">

                        <div class="card-body">

                            <?php if ($p['preco_final'] < $p['preco']): ?>
                                <span class="badge-promo">Promoção</span>
                            <?php endif; ?>

                            <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                            <p class="card-categoria"><?= htmlspecialchars($p['categoria']) ?></p>

                            <?php if ($p['preco_final'] < $p['preco']): ?>
                                <p class="card-preco-original">
                                    R$ <?= number_format($p['preco'], 2, ',', '.') ?>
                                </p>
                            <?php endif; ?>

                            <p class="card-preco-final">
                                R$ <?= number_format($p['preco_final'], 2, ',', '.') ?>
                            </p>

                            <?php if ($p['estoque'] === 0): ?>
                                <span class="badge-estoque bg-danger">● Esgotado</span>
                            <?php else: ?>
                                <span class="badge-estoque bg-success">● Em estoque: <?= $p['estoque'] ?></span>
                            <?php endif; ?>

                        </div>

                        <div class="card-footer">
                            <a href="gerenciar_favoritos.php?sku=<?= $p['sku'] ?>&acao=remove"
                               class="btn btn-warning btn-w-full">🌟 Remover dos Favoritos</a>
                        </div>

                    </div>
                    <?php endforeach; unset($p); ?>
                </div>
            <?php endif; ?>
        </section>

        <div style="height:80px"></div>

    </div>

    <script src="script.js"></script>
</body>
</html>
