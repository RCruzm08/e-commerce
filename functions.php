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
