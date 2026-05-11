<?php
require_once "functions.php";
$produtos = getProdutos();
$busca = $_GET['busca'] ?? "";
$cat = $_GET['cat'] ?? "";
$ordem = $_GET['ordem'] ?? "";

$produtosFiltrados = filtrarEOrdenar($produtos, $busca, $cat, $ordem);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-commerce Relampago Maccquin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .card-esgotado { opacity:0.6; filter:grayscale(1); }
    .badge-promo { position:absolute; top:10px; right:10px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <span class="navbar-brand">Loja Maccquin Tech 1.0</span>
        </div>
    </nav>
    <div class="container">
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
                    <option value="Hardware"   <?= $cat === "Hardware"    ? "selected" : "" ?>>Hardware</option>
                    <option value="Monitores"  <?= $cat === "Monitores"   ? "selected" : "" ?>>Monitores</option>
                    <option value="Perifericos"<?= $cat === "Perifericos" ? "selected" : "" ?>>Periféricos</option>
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

        <div class="row g-4">
            <?php foreach ($produtosFiltrados as $p): ?>
                <div class="col-md-4">
                    <div class="card h-100 position-relative <?= $p['estoque'] === 0 ? 'card-esgotado' : '' ?>">
                        <div class="card-body">
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
