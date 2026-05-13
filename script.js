document.addEventListener("DOMContentLoaded", () => {

    const badgeCarrinho      = document.getElementById("badge-carrinho");
    const badgeFavoritos     = document.getElementById("badge-favoritos");
    const carrinhoGrid       = document.getElementById("carrinho-grid");
    const carrinhoVazio      = document.getElementById("carrinho-vazio");
    const carrinhoTotal      = document.getElementById("carrinho-total");
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
        btn.style.opacity       = "0.55";
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
        carrinhoGrid.querySelectorAll("[data-preco-final]").forEach(el => {
            total += parseFloat(el.dataset.precoFinal) || 0;
        });
        carrinhoTotalValor.textContent = `R$ ${formatarPreco(total)}`;
        const vazio = carrinhoGrid.querySelectorAll(".card").length === 0;
        carrinhoVazio.classList.toggle("d-none", !vazio);
        carrinhoTotal.classList.toggle("d-none", vazio);
    }

    function criarCardCarrinho(sku, nome, categoria, preco, precoFinal) {
        const temDesconto = precoFinal < preco;

        const wrap = document.createElement("div");
        wrap.id               = `carrinho-item-${sku}`;
        wrap.dataset.precoFinal = precoFinal;

        wrap.innerHTML = `
            <div class="card border-success">
                <div class="card-body">
                    ${temDesconto ? '<span class="badge-promo">Promoção</span>' : ''}
                    <h5 class="card-title">${nome}</h5>
                    <p class="card-categoria">${categoria}</p>
                    ${temDesconto
                        ? `<p class="card-preco-original">R$ ${formatarPreco(preco)}</p>`
                        : ''}
                    <p class="card-preco-final">R$ ${formatarPreco(precoFinal)}</p>
                </div>
                <div class="card-footer">
                    <a href="gerenciar_carrinho.php?sku=${sku}&acao=remove"
                       class="btn btn-outline-danger btn-w-full btn-carrinho"
                       data-sku="${sku}"
                       data-acao="remove"
                       data-contexto="carrinho">🗑 Remover do Carrinho</a>
                </div>
            </div>
        `;

        wrap.querySelector(".btn-carrinho").addEventListener("click", handleCarrinho);
        carrinhoGrid.appendChild(wrap);
    }

    function handleCarrinho(e) {
        e.preventDefault();

        const btn     = e.currentTarget;
        const sku     = btn.dataset.sku;
        const acao    = btn.dataset.acao;

        const cardListagem  = document.querySelector(`.card[data-sku="${sku}"]`);
        const badgeEstoque  = cardListagem?.querySelector(".badge-estoque");
        const estoqueAtual  = parseInt(cardListagem?.dataset.estoque) || 0;

        bloquearBotao(btn, "Processando...");

        fetch(`gerenciar_carrinho.php?sku=${sku}&acao=${acao}`)
            .then(() => {

                if (acao === "add") {

                    const novoEstoque = estoqueAtual - 1;

                    if (cardListagem) cardListagem.dataset.estoque = novoEstoque;

                    if (novoEstoque <= 0) {

                        if (badgeEstoque) {
                            badgeEstoque.textContent = "● Esgotado";
                            badgeEstoque.classList.remove("bg-success");
                            badgeEstoque.classList.add("bg-danger");
                        }

                        cardListagem?.classList.add("card-esgotado");
                        btn.remove();

                    } else {

                        if (badgeEstoque) {
                            badgeEstoque.textContent = `● Em estoque: ${novoEstoque}`;
                            badgeEstoque.classList.remove("bg-danger");
                            badgeEstoque.classList.add("bg-success");
                        }

                        btn.textContent         = "✓ No Carrinho — Remover";
                        btn.className           = "btn btn-warning btn-w-full btn-carrinho";
                        btn.dataset.acao        = "remove";
                        btn.style.pointerEvents = "";
                        btn.style.opacity       = "";
                    }

                    const nome       = cardListagem?.dataset.nome     ?? sku;
                    const categoria  = cardListagem?.dataset.categoria ?? "";
                    const preco      = parseFloat(cardListagem?.dataset.preco)      || 0;
                    const precoFinal = parseFloat(cardListagem?.dataset.precoFinal) || 0;

                    criarCardCarrinho(sku, nome, categoria, preco, precoFinal);
                    atualizarBadge(badgeCarrinho, +1);

                } else {

                    document.getElementById(`carrinho-item-${sku}`)?.remove();

                    if (cardListagem) {
                        const novoEstoque = estoqueAtual + 1;
                        cardListagem.dataset.estoque = novoEstoque;

                        if (badgeEstoque) {
                            badgeEstoque.textContent = `● Em estoque: ${novoEstoque}`;
                            badgeEstoque.classList.remove("bg-danger");
                            badgeEstoque.classList.add("bg-success");
                        }

                        cardListagem.classList.remove("card-esgotado");

                        let btnListagem = cardListagem.querySelector(".btn-carrinho");

                        if (!btnListagem) {
                            const grupo = cardListagem.querySelector(".card-btn-group");
                            btnListagem = document.createElement("a");
                            btnListagem.href      = `gerenciar_carrinho.php?sku=${sku}&acao=add`;
                            btnListagem.className = "btn btn-success btn-w-full btn-carrinho";
                            btnListagem.dataset.sku  = sku;
                            btnListagem.dataset.acao = "add";
                            btnListagem.textContent  = "🛒 Comprar";
                            btnListagem.addEventListener("click", handleCarrinho);
                            grupo?.appendChild(btnListagem);
                        } else {
                            btnListagem.textContent         = "🛒 Comprar";
                            btnListagem.className           = "btn btn-success btn-w-full btn-carrinho";
                            btnListagem.dataset.acao        = "add";
                            btnListagem.style.pointerEvents = "";
                            btnListagem.style.opacity       = "";
                        }
                    }

                    atualizarBadge(badgeCarrinho, -1);
                }

                recalcularTotal();
            })
            .catch(() => {
                btn.style.pointerEvents = "";
                btn.style.opacity       = "";
                btn.textContent         = "Erro — tente novamente";
            });
    }

    document.querySelectorAll(".btn-carrinho").forEach(btn => {
        btn.addEventListener("click", handleCarrinho);
    });

    document.querySelectorAll(".btn-favorito").forEach(btn => {
        btn.addEventListener("click", function(e) {
            e.preventDefault();

            const sku  = this.dataset.sku;
            const acao = this.dataset.acao;
            const card = this.closest(".card");

            bloquearBotao(this, "Processando...");

            fetch(`gerenciar_favoritos.php?sku=${sku}&acao=${acao}`)
                .then(() => {
                    if (acao === "add") {
                        card.classList.add("border-warning");
                        this.textContent         = "🌟 Remover dos Favoritos";
                        this.className           = "btn btn-warning btn-w-full btn-favorito";
                        this.dataset.acao        = "remove";
                        this.style.pointerEvents = "";
                        this.style.opacity       = "";
                        atualizarBadge(badgeFavoritos, +1);
                    } else {
                        card.classList.remove("border-warning");
                        this.textContent         = "⭐ Favoritar";
                        this.className           = "btn btn-outline-warning btn-w-full btn-favorito";
                        this.dataset.acao        = "add";
                        this.style.pointerEvents = "";
                        this.style.opacity       = "";
                        atualizarBadge(badgeFavoritos, -1);
                    }
                })
                .catch(() => {
                    this.style.pointerEvents = "";
                    this.style.opacity       = "";
                    this.textContent         = "Erro — tente novamente";
                });
        });
    });

    const toggleTheme = document.getElementById("toggle-theme");

    function aplicarTema(tema) {
        if (tema === "light") {
            document.body.classList.add("light-mode");
            toggleTheme.textContent = "☀️";
        } else {
            document.body.classList.remove("light-mode");
            toggleTheme.textContent = "🌙";
        }
    }

    const temaSalvo = localStorage.getItem("tema") || "dark";
    aplicarTema(temaSalvo);

    toggleTheme.addEventListener("click", () => {
        const novoTema = document.body.classList.contains("light-mode") ? "dark" : "light";
        localStorage.setItem("tema", novoTema);
        aplicarTema(novoTema);
    });

});
