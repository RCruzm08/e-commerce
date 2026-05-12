const botoes = document.querySelectorAll(".btn-add");

botoes.forEach(botao => {
    botao.addEventListener("click", function () {
        const badgeEstoque = this.closest(".card-body").querySelector(".badge.bg-success");

        const estoqueAtual = parseInt(badgeEstoque.textContent.replace("Em estoque: ", ""));
        const novoEstoque = estoqueAtual - 1;

        if (novoEstoque <= 0) {
            badgeEstoque.textContent = "Esgotado";
            badgeEstoque.classList.replace("bg-success", "bg-danger");
            this.remove();
        } else {
            badgeEstoque.textContent = `Em estoque: ${novoEstoque}`;
        }

        this.innerText = "No Carrinho ✓";
        this.classList.replace("btn-success", "btn-warning");
        this.disabled = true;
    });
});
