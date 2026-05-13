<?php
session_start();
require_once "functions.php";

$produtos  = getProdutos();
$feedback  = $_SESSION['estoque_feedback'] ?? null;
unset($_SESSION['estoque_feedback']);

$criticos  = getProdutosEstoqueCritico($produtos, 5);
$stats     = getEstatisticasCatalogo($produtos);

// Agrupa por categoria para o select do formulário
$categorias = array_unique(array_column($produtos, 'categoria'));
sort($categorias);

// Filtra busca do painel
$busca    = sanitizarString($_GET['busca'] ?? "");
$catFiltro = sanitizarString($_GET['cat']  ?? "");

$produtosFiltrados = filtrarEOrdenar($produtos, $busca, $catFiltro, "az");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Estoque | Maccquin Tech</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ─── Admin-only overrides ──────────────────────────── */
        .admin-header {
            padding: 48px 0 40px;
            border-bottom: 1px solid var(--border-subtle);
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--text-primary);
        }

        .admin-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 6px;
        }

        /* ─── Stats strip ───────────────────────────────────── */
        .stats-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-normal);
            border-radius: var(--radius-lg);
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .stat-value {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.04em;
            color: var(--text-primary);
        }

        .stat-card.accent .stat-value { color: var(--accent); }
        .stat-card.gold   .stat-value { color: var(--gold); }
        .stat-card.danger .stat-value { color: var(--danger); }

        /* ─── Feedback toast ────────────────────────────────── */
        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: var(--radius-md);
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 32px;
            border: 1px solid transparent;
        }

        .toast.sucesso {
            background: var(--accent-dim);
            border-color: rgba(0,232,122,0.25);
            color: var(--accent);
        }

        .toast.erro {
            background: var(--danger-dim);
            border-color: rgba(255,77,106,0.25);
            color: var(--danger);
        }

        /* ─── Alertas críticos ──────────────────────────────── */
        .alertas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            margin-bottom: 40px;
        }

        .alerta-card {
            background: var(--bg-surface);
            border: 1px solid rgba(255,77,106,0.3);
            border-radius: var(--radius-lg);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .alerta-card.esgotado {
            border-color: rgba(255,77,106,0.5);
            background: var(--danger-dim);
        }

        .alerta-nome {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--text-primary);
            line-height: 1.3;
        }

        .alerta-sku {
            font-size: 0.72rem;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .alerta-qtd {
            font-family: var(--font-display);
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--danger);
            white-space: nowrap;
        }

        .alerta-qtd.zero { color: var(--danger); }

        /* ─── Tabela de produtos ────────────────────────────── */
        .table-wrap {
            background: var(--bg-surface);
            border: 1px solid var(--border-normal);
            border-radius: var(--radius-xl);
            overflow: hidden;
            margin-bottom: 80px;
        }

        .table-toolbar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-subtle);
            flex-wrap: wrap;
        }

        .table-toolbar .form-control,
        .table-toolbar .form-select {
            flex: 1;
            min-width: 160px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.88rem;
        }

        thead th {
            padding: 14px 20px;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-subtle);
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid var(--border-subtle);
            transition: background var(--transition);
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg-elevated); }

        tbody td {
            padding: 16px 20px;
            color: var(--text-primary);
            vertical-align: middle;
        }

        .td-sku {
            font-family: monospace;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .td-nome { font-weight: 600; }

        .td-cat {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .td-preco {
            font-family: var(--font-display);
            font-weight: 700;
            color: var(--accent);
        }

        .td-estoque { font-weight: 700; }
        .td-estoque.critico { color: var(--danger); }
        .td-estoque.baixo   { color: var(--gold); }
        .td-estoque.ok      { color: var(--accent); }

        /* ─── Form de edição de estoque (inline) ────────────── */
        .estoque-form {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .estoque-form input[type="number"] {
            width: 72px;
            padding: 7px 10px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-normal);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            transition: all var(--transition);
        }

        .estoque-form input[type="number"]:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-dim);
            outline: none;
        }

        .estoque-form select {
            padding: 7px 10px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-normal);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition);
        }

        .estoque-form select:focus {
            border-color: var(--accent);
            outline: none;
        }

        .btn-aplicar {
            padding: 7px 14px;
            font-size: 0.8rem;
            border-radius: var(--radius-sm);
        }

        /* ─── Seção titles ──────────────────────────────────── */
        .section-title {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-primary);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title .pill {
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            background: var(--danger-dim);
            color: var(--danger);
            border: 1px solid rgba(255,77,106,0.25);
            letter-spacing: 0.05em;
        }

        @media (max-width: 768px) {
            table { display: block; overflow-x: auto; }
            .estoque-form { flex-wrap: wrap; }
        }
    </style>
</head>
<body>

    <!-- ── Navbar ──────────────────────────────────────────────── -->
    <nav class="navbar">
        <div class="container">
            <span class="navbar-brand">Maccquin Tech</span>
            <div class="navbar-actions">
                <a href="index.php" class="btn btn-ghost btn-sm">← Voltar à loja</a>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- ── Cabeçalho ───────────────────────────────────────── -->
        <div class="admin-header">
            <h1>⚙️ Gerenciamento de Estoque</h1>
            <p>Adicione, subtraia ou redefina o estoque de qualquer produto do catálogo.</p>
        </div>

        <!-- ── Feedback ────────────────────────────────────────── -->
        <?php if ($feedback): ?>
            <div class="toast <?= $feedback['tipo'] ?>">
                <?= $feedback['tipo'] === 'sucesso' ? '✓' : '✕' ?>
                <?= htmlspecialchars($feedback['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- ── Stats ───────────────────────────────────────────── -->
        <div class="stats-strip">
            <div class="stat-card accent">
                <span class="stat-label">Total de produtos</span>
                <span class="stat-value"><?= $stats['total'] ?></span>
            </div>
            <div class="stat-card accent">
                <span class="stat-label">Em estoque</span>
                <span class="stat-value"><?= $stats['em_estoque'] ?></span>
            </div>
            <div class="stat-card danger">
                <span class="stat-label">Críticos (≤ 5)</span>
                <span class="stat-value"><?= count($criticos) ?></span>
            </div>
            <div class="stat-card gold">
                <span class="stat-label">Esgotados</span>
                <span class="stat-value"><?= count(array_filter($produtos, fn($p) => $p['estoque'] === 0)) ?></span>
            </div>
        </div>

        <!-- ── Alertas críticos ────────────────────────────────── -->
        <?php if (!empty($criticos)): ?>
        <div style="margin-bottom: 40px">
            <p class="section-title">
                Atenção necessária
                <span class="pill"><?= count($criticos) ?> produto<?= count($criticos) > 1 ? 's' : '' ?></span>
            </p>
            <div class="alertas-grid">
                <?php foreach ($criticos as $c): ?>
                <div class="alerta-card <?= $c['estoque'] === 0 ? 'esgotado' : '' ?>">
                    <div>
                        <div class="alerta-nome"><?= htmlspecialchars($c['nome']) ?></div>
                        <div class="alerta-sku"><?= $c['sku'] ?></div>
                    </div>
                    <div class="alerta-qtd <?= $c['estoque'] === 0 ? 'zero' : '' ?>">
                        <?= $c['estoque'] === 0 ? '—' : $c['estoque'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── Tabela de produtos ──────────────────────────────── -->
        <div class="table-wrap">

            <!-- Filtro rápido -->
            <form method="get" class="table-toolbar">
                <input type="text" name="busca" class="form-control"
                       placeholder="Buscar por nome..."
                       value="<?= htmlspecialchars($busca) ?>">

                <select name="cat" class="form-select" style="max-width:200px">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c ?>" <?= $catFiltro === $c ? 'selected' : '' ?>><?= $c ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-submit btn-sm">Filtrar</button>

                <?php if ($busca || $catFiltro): ?>
                    <a href="admin.php" class="btn btn-ghost btn-sm">Limpar</a>
                <?php endif; ?>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th>Ajustar estoque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produtosFiltrados as $p):
                        $status = getStatusEstoque($p);
                        $classeEstoque = match($status) {
                            'esgotado', 'critico' => 'critico',
                            'baixo'               => 'baixo',
                            default               => 'ok',
                        };
                    ?>
                    <tr>
                        <td class="td-sku"><?= $p['sku'] ?></td>
                        <td class="td-nome"><?= htmlspecialchars($p['nome']) ?></td>
                        <td class="td-cat"><?= htmlspecialchars($p['categoria']) ?></td>
                        <td class="td-preco"><?= formatarMoeda($p['preco']) ?></td>
                        <td class="td-estoque <?= $classeEstoque ?>"><?= $p['estoque'] ?></td>
                        <td>
                            <form method="post" action="gerenciar_estoque.php" class="estoque-form">
                                <input type="hidden" name="sku" value="<?= $p['sku'] ?>">

                                <select name="acao">
                                    <option value="add">+ Adicionar</option>
                                    <option value="sub">− Subtrair</option>
                                    <option value="set">= Definir</option>
                                </select>

                                <input type="number" name="quantidade" value="1" min="0" max="9999">

                                <button type="submit" class="btn btn-success btn-aplicar">Aplicar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>

    </div>

    <script>
        // Auto-dismiss do toast após 4 segundos
        const toast = document.querySelector('.toast');
        if (toast) {
            setTimeout(() => {
                toast.style.transition = 'opacity 0.4s ease';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }
    </script>

</body>
</html>