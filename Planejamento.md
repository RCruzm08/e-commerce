# Projeto Integrador - Especificacao do E-commerce Educacional

**Visao Geral:** O Projeto Integrador e a simulacao de uma plataforma de E-commerce realista, desenvolvida como avaliacao pratica conclusiva do curso de Logica de Programacao. O sistema sera construido em PHP (backend), utilizando estruturas de dados em memoria e arquivos JSON, sem a necessidade inicial de um banco de dados relacional. O foco e aplicar os conceitos de arrays multidimensionais, algoritmos de busca e ordenacao, modularizacao e arquitetura de codigo limpo.

## 1. Arquitetura e Configuracao do Ambiente

- [] **Configuracao do Servidor Local:** Estruturacao da pasta raiz do projeto e inicializacao do servidor embutido do PHP (`php -S localhost:8000`) para simular um ambiente de hospedagem.
- [] **Separacao de Preocupacoes (MVC Basico):** Divisao inicial do projeto separando a logica de processamento de dados (backend) da estrutura de renderizacao visual (frontend) dentro do mesmo arquivo `index.php`.
- [] **Base de Dados em Memoria (Fase 1):** Declaracao do catalogo inicial de produtos atraves de um Array Multidimensional Associativo diretamente no codigo PHP para validar a logica de renderizacao.
- [] **Base de Dados JSON (Fase 2):** Refatoracao do sistema para desacoplar os dados. Criacao do arquivo `produtos.json` e implementacao da funcao `file_get_contents` combinada com `json_decode(..., true)` para ler e popular a matriz de produtos no backend.
- [] **Estrutura Base HTML/CSS:** Desenvolvimento do layout estatico do E-commerce (cabecalho, barra lateral de filtros e container da vitrine) utilizando HTML5 e CSS simples embutido (ou framework de preferencia) para preparar o terreno da injecao dinamica.

## 2. Motor de Renderizacao e Interface da Vitrine

- [] **Laco de Renderizacao Dinamica:** Implementacao do comando `foreach` para varrer a matriz de produtos lida do JSON e gerar os blocos visuais (`divs` ou `cards`) no HTML.
- [] **Injecao de Dados no Frontend:** Exibicao do titulo, categoria e preco formatado (`number_format`) dentro de cada cartao de produto.
- [] **Regra de Negocio de Estoque:** Logica condicional (`if/else`) dentro do laco de renderizacao para validar o atributo `estoque`.
    - Exibe o botao verde "Adicionar ao Carrinho" caso o estoque seja maior que zero.
    - Exibe um bloco visual inativo e acinzentado escrito "Produto Esgotado" caso o estoque seja zero.
- [] **Destaque Promocional Automatico:** Regra de negocio condicional que identifica produtos de uma categoria especifica (ex: Perifericos) e aplica um selo visual de "Promocao" no momento da renderizacao.

## 3. Painel de Controle e Filtros Multicriterio

- [ ] **Interface do Formulario Analitico:** Criacao do formulario HTML (metodo GET) no topo da pagina contendo:
    - Campo de busca textual por nome do produto.
    - Selecionador de categoria (Select).
    - Entradas numericas para definicao de faixa de preco (Minimo e Maximo).
    - Selecionador de ordenacao de resultados.
- [ ] **Captura Segura de Dados (Backend):** Implementacao de variaveis receptoras no PHP (`$_GET`) utilizando validacoes (`isset` ou Operador de Coalescencia Nula `??`) para evitar erros quando a pagina carrega pela primeira vez.
- [ ] **O Motor de Filtragem Simultanea:** Construcao da estrutura analitica que percorre todo o catalogo (JSON) e testa cada produto contra as regras definidas pelo usuario:
    - Verificacao de String: Aplicacao da funcao `stripos` para buscas de texto parciais e insensiveis a maiusculas/minusculas.
    - Verificacao de Categoria: Condicao de igualdade ou "Todos".
    - Verificacao Numerica: Testes de maior ou igual para preco minimo e menor ou igual para preco maximo.
- [ ] **Criacao da Matriz Resultante:** Acumulacao dos produtos que sobreviveram a todas as validacoes logicas em um novo array chamado `$produtosVitrine`, que sera enviado para a tela.

## 4. Algoritmos de Ordenacao e Tratamento de Excecoes

- [ ] **Logica de Inversao de Demanda (SWAP):** Implementacao do operador auxiliar para permitir a troca de posicoes de itens dentro da matriz durante o processo de ordenacao.
- [ ] **Implementacao do Bubble Sort Dinamico:** Programacao do laco de ordenacao focado na variavel `$produtosVitrine`.
- [ ] **Selecao Condicional de Ordem:** Regra de negocio (`if`) que verifica a escolha do usuario no formulario e altera o operador matematico do algoritmo (maior que ou menor que) para ordenar do mais barato para o mais caro, ou vice-versa.
- [ ] **Tratamento de Pesquisa Vazia (UX):** Estrutura condicional (`if count > 0`) envolvendo a area da vitrine no HTML. Caso a filtragem nao encontre produtos, o sistema bloqueia o laco de renderizacao e exibe uma mensagem elegante de erro tratada ("Nenhum produto atendeu aos criterios.").

## 5. Auditoria Financeira e Calculos Automaticos

- [ ] **O Acumulador de Patrimonio:** Criacao de uma variavel acumuladora (inicializada em zero) antes da execucao do laco de renderizacao da vitrine.
- [ ] **Calculo de Estoque em Tempo Real:** Operacao matematica inserida dentro do laco de renderizacao que multiplica a quantidade em estoque pelo preco do produto.
- [ ] **Apresentacao de Rodape:** Exibicao do valor total em dinheiro somado na variavel acumuladora ao final da pagina, simulando o valor total dos ativos do E-commerce disponiveis na busca atual.

## 6. Modularizacao, Clean Code e Documentacao

- [ ] **Isolamento de Funcoes Matematicas:** Extracao do calculo de desconto promocional (Regra de Negocio) e alocacao dentro de uma funcao PHP dedicada e externa, simplificando o fluxo do codigo principal.
- [ ] **Refatoracao de Variaveis:** Auditoria em todas as variaveis do projeto para garantir nomenclaturas descritivas no padrao camelCase.
- [ ] **Padronizacao Visual (Indentacao):** Garantia de alinhamento estrutural, verificando aberturas e fechamentos de estruturas HTML e PHP, eliminando codigo "grudado".
- [ ] **Documentacao Tecnica (DocBlock):** Implementacao de cabecalhos de documentacao no inicio do script, detalhando a funcao do arquivo, autor e dependencias.
- [ ] **Comentarios Funcionais:** Insercao de blocos curtos de explicacao logica (`//`) acima dos lacos de repeticao, algoritmos de busca e calculos financeiros complexos.

## 7. Apresentacao e Defesa do Software (Avaliacao)

- [ ] **Simulacao de Caso de Uso Real:** Navegacao no sistema durante a entrega demonstrando todos os fluxos: pesquisa generica, pesquisa com filtros nao atendidos e pesquisas super-restritivas.
- [ ] **Defesa da Arquitetura Logica:** Explicacao estruturada de como o codigo isola um produto do catalogo que nao obedece ao filtro de preco.
- [ ] **Prova de Resiliencia (Error Handling):** Submissao do formulario com parametros em branco ou tipos incorretos para demonstrar que o sistema nao apresenta erros brutos (Warnings/Fatal Errors) na tela do usuario.