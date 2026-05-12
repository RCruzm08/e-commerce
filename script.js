document.addEventListener("DOMContentLoaded", () => {

    const badgeCarrinho   = document.getElementById("badge-carrinho");
    const badgeFavoritos  = document.getElementById("badge-favoritos");
    const carrinhoGrid    = document.getElementById("carrinho-grid");
    const carrinhoVazio   = document.getElementById("carrinho-vazio");
    const carrinhoTotal   = document.getElementById("carrinho-total");
    const carrinhoTotalValor = document.getElementById("carrinho-total-valor");

    function atualizarBadge(badge, delta) {
        const atual = parseInt(badge.textContent) || 0;
        const novo  = atual + delta;

        badge.textContent = novo <= 0 ? "0" : novo;

        novo <= 0
            ? badge.classList.add("d-none")
            : badge.classList.remove("d-none");
    }

    function bloquearBotao(btn, texto) {
        btn.style.pointerEvents = "none";
        btn.style.opacity       = "0.6";
        btn.textContent         = texto;
    }

    function formatarPreco(valor) {
        return valor.toLocaleString("pt-BR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function recalcularTotal() {

        let total = 0;

        carrinhoGrid.querySelectorAll(".col-md-4").forEach(col => {
            const preco = parseFloat(col.dataset.precoFinal) || 0;
            total += preco;
        });

        carrinhoTotalValor.textContent = `R$ ${formatarPreco(total)}`;

        const vazio = carrinhoGrid.querySelectorAll(".col-md-4").length === 0;

        carrinhoVazio.classList.toggle("d-none", !vazio);
        carrinhoTotal.classList.toggle("d-none", vazio);
    }

    function criarCardCarrinho(sku, nome, categoria, preco, precoFinal) {

        const temDesconto = precoFinal < preco;

        const col = document.createElement("div");

        col.className = "col-md-4";
        col.id        = `carrinho-item-${sku}`;
        col.dataset.precoFinal = precoFinal;

        col.innerHTML = `
            <div class="card h-100 border-success position-relative">

                <div class="card-body d-flex flex-column">

                    ${temDesconto
                        ? '<span class="badge bg-warning text-dark badge-promo">Promoção</span>'
                        : ''}

                    <h5 class="card-title">${nome}</h5>

                    <p class="text-muted mb-1">${categoria}</p>

                    ${temDesconto
                        ? `<p class="text-decoration-line-through text-muted mb-0">
                            R$ ${formatarPreco(preco)}
                           </p>`
                        : ''}

                    <p class="fw-bold text-success">
                        R$ ${formatarPreco(precoFinal)}
                    </p>

                </div>

                <div class="card-footer">

                    <a href="gerenciar_carrinho.php?sku=${sku}&acao=remove"
                       class="btn btn-outline-danger w-100 btn-carrinho"
                       data-sku="${sku}"
                       data-acao="remove"
                       data-contexto="carrinho">

                       🗑 Remover do Carrinho

                    </a>

                </div>

            </div>
        `;

        col.querySelector(".btn-carrinho")
            .addEventListener("click", handleCarrinho);

        return col;
    }

    function handleCarrinho(e) {

        e.preventDefault();

        const btn       = e.currentTarget;
        const sku       = btn.dataset.sku;
        const acao      = btn.dataset.acao;

        const cardListagem = document.querySelector(`.card[data-sku="${sku}"]`);

        const badgeEstoque = cardListagem?.querySelector(".badge-estoque");

        const estoqueAtual = parseInt(cardListagem?.dataset.estoque) || 0;

        bloquearBotao(btn, "Processando...");

        fetch(`gerenciar_carrinho.php?sku=${sku}&acao=${acao}`)
            .then(() => {

              
                if (acao === "add") {

                    const novoEstoque = estoqueAtual - 1;

                    if (cardListagem) {
                        cardListagem.dataset.estoque = novoEstoque;
                    }

                    
                    if (novoEstoque <= 0) {

                        if (badgeEstoque) {

                            badgeEstoque.textContent = "Esgotado";

                            badgeEstoque.classList.remove("bg-success");
                            badgeEstoque.classList.add("bg-danger");
                        }

                        cardListagem.classList.add("card-esgotado");

                        btn.remove();

                    } else {

                        if (badgeEstoque) {

                            badgeEstoque.textContent =
                                `Em estoque: ${novoEstoque}`;

                            badgeEstoque.classList.remove("bg-danger");
                            badgeEstoque.classList.add("bg-success");
                        }

                        btn.textContent         = "✓ No Carrinho — Remover";
                        btn.className           = "btn btn-warning w-100 btn-carrinho";
                        btn.dataset.acao        = "remove";
                        btn.style.pointerEvents = "";
                        btn.style.opacity       = "";
                    }

                    const nome       = cardListagem?.dataset.nome ?? sku;
                    const categoria  = cardListagem?.dataset.categoria ?? "";
                    const preco      = parseFloat(cardListagem?.dataset.preco) || 0;
                    const precoFinal = parseFloat(cardListagem?.dataset.precoFinal) || 0;

                    const cardCarrinho = criarCardCarrinho(
                        sku,
                        nome,
                        categoria,
                        preco,
                        precoFinal
                    );

                    carrinhoGrid.appendChild(cardCarrinho);

                    atualizarBadge(badgeCarrinho, +1);
                }

             
                else {

                    const itemCarrinho =
                        document.getElementById(`carrinho-item-${sku}`);

                    if (itemCarrinho) {
                        itemCarrinho.remove();
                    }

                    if (cardListagem) {

                        const novoEstoque = estoqueAtual + 1;

                        cardListagem.dataset.estoque = novoEstoque;

                        if (badgeEstoque) {

                            badgeEstoque.textContent =
                                `Em estoque: ${novoEstoque}`;

                            badgeEstoque.classList.remove("bg-danger");
                            badgeEstoque.classList.add("bg-success");
                        }

                        cardListagem.classList.remove("card-esgotado");
                    }

                    if (cardListagem) {

                        let btnListagem =
                            cardListagem.querySelector(".btn-carrinho");

                        if (!btnListagem) {

                            const divBtns =
                                cardListagem.querySelector(".mt-3");

                            btnListagem = document.createElement("a");

                            btnListagem.href =
                                `gerenciar_carrinho.php?sku=${sku}&acao=add`;

                            btnListagem.className =
                                "btn btn-success w-100 btn-carrinho";

                            btnListagem.dataset.sku   = sku;
                            btnListagem.dataset.acao  = "add";

                            btnListagem.textContent = "🛒 Comprar";

                            btnListagem.addEventListener(
                                "click",
                                handleCarrinho
                            );

                            divBtns.appendChild(btnListagem);

                        } else {

                            btnListagem.textContent = "🛒 Comprar";

                            btnListagem.className =
                                "btn btn-success w-100 btn-carrinho";

                            btnListagem.dataset.acao = "add";

                            btnListagem.style.pointerEvents = "";
                            btnListagem.style.opacity = "";
                        }
                    }

                    atualizarBadge(badgeCarrinho, -1);
                }

                recalcularTotal();
            })

            .catch(() => {

                btn.style.pointerEvents = "";
                btn.style.opacity       = "";

                btn.textContent =
                    "Erro — tente novamente";
            });
    }

    document.querySelectorAll(".btn-carrinho").forEach(btn => {
        btn.addEventListener("click", handleCarrinho);
    });

  
    document.querySelectorAll(".btn-favorito").forEach(btn => {

        btn.addEventListener("click", function (e) {

            e.preventDefault();

            const sku  = this.dataset.sku;
            const acao = this.dataset.acao;

            const card = this.closest(".card");

            bloquearBotao(this, "Processando...");

            fetch(`gerenciar_favoritos.php?sku=${sku}&acao=${acao}`)

                .then(() => {

                    if (acao === "add") {

                        card.classList.add("border-warning");

                        this.textContent =
                            "🌟 Remover dos Favoritos";

                        this.className =
                            "btn btn-warning w-100 btn-favorito";

                        this.dataset.acao = "remove";

                        this.style.pointerEvents = "";
                        this.style.opacity = "";

                        atualizarBadge(badgeFavoritos, +1);

                    } else {

                        card.classList.remove("border-warning");

                        this.textContent =
                            "⭐ Favoritar";

                        this.className =
                            "btn btn-outline-warning w-100 btn-favorito";

                        this.dataset.acao = "add";

                        this.style.pointerEvents = "";
                        this.style.opacity = "";

                        atualizarBadge(badgeFavoritos, -1);
                    }
                })

                .catch(() => {

                    this.style.pointerEvents = "";
                    this.style.opacity = "";

                    this.textContent =
                        "Erro — tente novamente";
                });
        });
    });

});

const toggleTheme = document.getElementById("toggle-theme");

function aplicarTema(tema) {

    if (tema === "light") {

        document.body.classList.add("light-mode");

        toggleTheme.textContent = "☀️ ";

    } else {

        document.body.classList.remove("light-mode");

        toggleTheme.textContent = "🌙 ";
    }
}

const temaSalvo = localStorage.getItem("tema") || "dark";

aplicarTema(temaSalvo);

toggleTheme.addEventListener("click", () => {

    const temaAtual = document.body.classList.contains("light-mode")
        ? "light"
        : "dark";

    const novoTema = temaAtual === "dark"
        ? "light"
        : "dark";

    localStorage.setItem("tema", novoTema);

    aplicarTema(novoTema);
});
