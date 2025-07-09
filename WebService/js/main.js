document.addEventListener('DOMContentLoaded', function () {
    const apiUrl = 'api/clientes/index.php'; // URL direta para o arquivo PHP
    const form = document.getElementById('form-cliente');
    const tabelaCorpo = document.querySelector('#tabela-clientes tbody');
    const clienteIdInput = document.getElementById('cliente-id');
    const btnSalvar = document.getElementById('btn-salvar');
    const btnCancelar = document.getElementById('btn-cancelar');
    const campoBusca = document.getElementById('campo-busca');

    // Função para carregar e exibir os clientes
    async function carregarClientes(termoBusca = '') {
        let url = apiUrl;
        if (termoBusca) {
            url += `?busca=${encodeURIComponent(termoBusca)}`; // A busca já usa o formato correto
        }

        try {
            const response = await fetch(url);
            if (!response.ok) {
                if (response.status === 404) {
                    tabelaCorpo.innerHTML = '<tr><td colspan="4">Nenhum cliente encontrado.</td></tr>';
                } else {
                    tabelaCorpo.innerHTML = `<tr><td colspan="4">Erro ao carregar: ${response.statusText}</td></tr>`;
                }
                return;
            }
            const clientes = await response.json();

            tabelaCorpo.innerHTML = ''; // Limpa a tabela
            clientes.forEach(cliente => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${cliente.nome}</td>
                    <td>${cliente.email}</td>
                    <td>${cliente.telefone || ''}</td>
                    <td>
                        <button class="btn-acao btn-editar" data-id="${cliente.id}">Editar</button>
                        <button class="btn-acao btn-excluir" data-id="${cliente.id}">Excluir</button>
                    </td>
                `;
                tabelaCorpo.appendChild(tr);
            });
        } catch (error) {
            console.error('Erro ao carregar clientes:', error);
            tabelaCorpo.innerHTML = '<tr><td colspan="4">Erro de conexão. Verifique o console.</td></tr>';
        }
    }

    // Função para resetar o formulário
    function resetarFormulario() {
        form.reset();
        clienteIdInput.value = '';
        btnSalvar.textContent = 'Adicionar Cliente';
        btnCancelar.classList.add('hidden');
    }

    // Evento de submit do formulário (Adicionar ou Atualizar)
    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const id = clienteIdInput.value;
        const clienteData = {
            nome: document.getElementById('nome').value,
            email: document.getElementById('email').value,
            telefone: document.getElementById('telefone').value,
        };

        let url = apiUrl;
        let method = 'POST';

        if (id) { // Se tem ID, é uma atualização (PUT)
            url = `${apiUrl}?id=${id}`; // Altera a URL para usar parâmetro
            method = 'PUT';
        }

        const headers = {
            'Content-Type': 'application/json',
            'X-API-KEY': 'sua-chave-secreta-aqui-12345'
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: headers,
                body: JSON.stringify(clienteData)
            });

            const result = await response.json();
            alert(result.message);

            if (response.ok || response.status === 201) {
                resetarFormulario();
                carregarClientes();
            }
        } catch (error) {
            console.error('Erro ao salvar cliente:', error);
            alert('Ocorreu um erro ao salvar. Verifique o console para mais detalhes.');
        }
    });

    // Eventos na tabela para os botões de Editar e Excluir
    tabelaCorpo.addEventListener('click', async function (e) {
        const target = e.target;
        if (!target.classList.contains('btn-acao')) return;

        const id = target.dataset.id;

        // Botão Editar
        if (target.classList.contains('btn-editar')) {
            try {
                const response = await fetch(`${apiUrl}?id=${id}`); // Altera a URL para usar parâmetro
                const cliente = await response.json();

                clienteIdInput.value = cliente.id;
                document.getElementById('nome').value = cliente.nome;
                document.getElementById('email').value = cliente.email;
                document.getElementById('telefone').value = cliente.telefone;

                btnSalvar.textContent = 'Salvar Alterações';
                btnCancelar.classList.remove('hidden');
                window.scrollTo(0, 0);
            } catch (error) {
                console.error('Erro ao buscar dados do cliente para edição:', error);
            }
        }

        // Botão Excluir
        if (target.classList.contains('btn-excluir')) {
            if (confirm('Tem certeza que deseja excluir este cliente?')) {
                try { // Para o DELETE, o ID será enviado no corpo da requisição, mas a URL precisa ser a base.
                    const response = await fetch(`${apiUrl}?id=${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-API-KEY': 'sua-chave-secreta-aqui-12345'
                        },
                    });

                    const result = await response.json();
                    alert(result.message);

                    if (response.ok) {
                        carregarClientes();
                    }
                } catch (error) {
                    console.error('Erro ao excluir cliente:', error);
                }
            }
        }
    });

    // Botão Cancelar Edição
    btnCancelar.addEventListener('click', resetarFormulario);

    // Evento para o campo de busca
    campoBusca.addEventListener('input', () => carregarClientes(campoBusca.value));

    // Carrega os clientes ao iniciar a página
    carregarClientes();
});