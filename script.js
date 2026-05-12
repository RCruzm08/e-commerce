const botoes = document.querySelectorAll(".btn-add");

botoes.forEach(botao => {
    botao.addEventListener('click', function () {

        const nomeProduto = this.getAttribute('data-nome');

        alert(`Sucesso: ${nomeProduto} foi adicionado ao seu carrinho`);

        this.innerText = "No Carrinho";

        this.classList.replace('btn-success', 'btn-warning');

    });
});
