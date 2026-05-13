<?php

// Carrega os dados do JSON e converte em array associativa
function getProdutos(): array {
    $json = file_get_contents("produtos.json");
    return json_decode($json, true);
}

// Calculo de descontos para os produtos
function calcularDesconto(float $preco, string $categoria): float {
    if ($categoria === "Perifericos" || $categoria === "Monitores") {
        return $preco * 0.90;
    }
    return $preco;
}

// Filtra e ordena os produtos da base de dados (JSON) || ("Motor de filtros e busca")
function filtrarEOrdenar(array $produtos, string $busca, string $categoria, string $ordem): array {

    $resultado = [];

    foreach ($produtos as $p) {

        // Verifica busca por nome
        $matchBusca = $busca === "" || stripos($p['nome'], $busca) !== false;

        // Verifica categoria
        $matchCategoria = $categoria === "" || $p['categoria'] === $categoria;

        // Se atender os filtros
        if ($matchBusca && $matchCategoria) {

            // Adiciona preço com desconto
            $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);
            $resultado[] = $p;
        }
    }

    // Ordenação
    usort($resultado, function(array $a, array $b) use ($ordem): int {
        return match($ordem) {
            "menor_preco" => $a['preco_final'] <=> $b['preco_final'],
            "maior_preco" => $b['preco_final'] <=> $a['preco_final'],
            "az"          => strcmp($a['nome'], $b['nome']),
            "za"          => strcmp($b['nome'], $a['nome']),
            default       => 0,
        };
    });

    return $resultado;
}

// Formata um valor float como moeda brasileira (R$ 1.299,90)
function formatarMoeda(float $valor): string {
    return "R$ " . number_format($valor, 2, ',', '.');
}

// Calcula o percentual de desconto entre o preço original e o preço final
function calcularPercentualDesconto(float $precoOriginal, float $precoFinal): int {
    if ($precoOriginal <= 0) return 0;
    return (int) round((1 - $precoFinal / $precoOriginal) * 100);
}

// Retorna o status de estoque de um produto como string legível
// Leva em conta o estoque atual da sessão, se disponível
function getStatusEstoque(array $produto, array $estoqueSession = []): string {
    $quantidade = $estoqueSession[$produto['sku']] ?? $produto['estoque'];

    if ($quantidade === 0)  return "esgotado";
    if ($quantidade <= 5)   return "critico";
    if ($quantidade <= 15)  return "baixo";
    return "disponivel";
}

// Calcula o valor total dos itens do carrinho com descontos aplicados
function calcularTotalCarrinho(array $skusCarrinho, array $produtos): float {
    $total = 0.0;

    foreach ($produtos as $p) {
        if (in_array($p['sku'], $skusCarrinho)) {
            $total += calcularDesconto($p['preco'], $p['categoria']);
        }
    }

    return $total;
}

// Retorna os produtos em destaque: os mais baratos de cada categoria (um por categoria)
function getProdutosDestaque(array $produtos): array {
    $porCategoria = [];

    foreach ($produtos as $p) {
        if ($p['estoque'] === 0) continue;

        $cat = $p['categoria'];
        $p['preco_final'] = calcularDesconto($p['preco'], $p['categoria']);

        if (!isset($porCategoria[$cat]) || $p['preco_final'] < $porCategoria[$cat]['preco_final']) {
            $porCategoria[$cat] = $p;
        }
    }

    return array_values($porCategoria);
}

// Verifica se um SKU específico está no carrinho da sessão
function estaNoCarrinho(string $sku, array $carrinho): bool {
    return in_array($sku, $carrinho);
}

// Verifica se um SKU específico está nos favoritos da sessão
function estaNosFavoritos(string $sku, array $favoritos): bool {
    return in_array($sku, $favoritos);
}

// Busca um produto pelo SKU na lista de produtos
// Retorna o produto encontrado ou null se não existir
function getProdutoPorSku(string $sku, array $produtos): ?array {
    foreach ($produtos as $p) {
        if ($p['sku'] === $sku) return $p;
    }
    return null;
}

// Agrupa os produtos por categoria e retorna um array associativo
// Chave: nome da categoria | Valor: array de produtos
function agruparPorCategoria(array $produtos): array {
    $grupos = [];

    foreach ($produtos as $p) {
        $grupos[$p['categoria']][] = $p;
    }

    return $grupos;
}

// Gera um resumo estatístico do catálogo de produtos
// Retorna total de produtos, valor médio, menor e maior preço
function getEstatisticasCatalogo(array $produtos): array {
    if (empty($produtos)) {
        return ['total' => 0, 'media' => 0.0, 'menor' => 0.0, 'maior' => 0.0, 'em_estoque' => 0];
    }

    $precos    = array_column($produtos, 'preco');
    $emEstoque = array_filter($produtos, fn($p) => $p['estoque'] > 0);

    return [
        'total'      => count($produtos),
        'media'      => array_sum($precos) / count($precos),
        'menor'      => min($precos),
        'maior'      => max($precos),
        'em_estoque' => count($emEstoque),
    ];
}

// Retorna os N produtos mais recentemente adicionados ao carrinho
// Preserva a ordem de inserção da sessão
function getItensCarrinhoOrdenados(array $skusCarrinho, array $produtos, int $limite = 0): array {
    $mapa = [];
    foreach ($produtos as $p) {
        $mapa[$p['sku']] = $p;
    }

    $resultado = [];
    foreach ($skusCarrinho as $sku) {
        if (isset($mapa[$sku])) {
            $item = $mapa[$sku];
            $item['preco_final'] = calcularDesconto($item['preco'], $item['categoria']);
            $resultado[] = $item;
        }
    }

    return $limite > 0 ? array_slice($resultado, 0, $limite) : $resultado;
}

// Sanitiza e valida um parâmetro de string vindo do GET/POST
// Retorna a string limpa ou uma string vazia se inválida
function sanitizarString(mixed $valor, int $maxLen = 100): string {
    if (!is_string($valor)) return "";
    return substr(trim(strip_tags($valor)), 0, $maxLen);
}

// Valida se uma ação recebida via GET é permitida
// Evita que valores arbitrários sejam processados nos gerenciadores
function validarAcao(string $acao, array $acoesPermitidas = ['add', 'remove']): bool {
    return in_array($acao, $acoesPermitidas, true);
}

// Persiste o array de produtos de volta no arquivo JSON
// Retorna true em caso de sucesso, false se não for possível gravar
function salvarProdutos(array $produtos): bool {
    $json = json_encode($produtos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents("produtos.json", $json) !== false;
}

// Retorna os produtos com estoque abaixo de um limite crítico
// Útil para exibir alertas no painel administrativo
function getProdutosEstoqueCritico(array $produtos, int $limite = 5): array {
    return array_values(
        array_filter($produtos, fn($p) => $p['estoque'] <= $limite)
    );
}
