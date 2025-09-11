document.addEventListener('DOMContentLoaded', () => {
    const pokemonSelect = document.getElementById('pokemon_id');
    const golpesContainer = document.getElementById('lista_golpes_container');

    if (pokemonSelect) {
        pokemonSelect.addEventListener('change', () => {
            const pokemonId = pokemonSelect.value;
            golpesContainer.innerHTML = 'A carregar...';

            if (!pokemonId) {
                golpesContainer.innerHTML = 'Selecione um Pokémon para ver a lista de golpes.';
                return;
            }

            // Faz o pedido à nossa nova API
            fetch(`api_get_moves.php?pokemon_id=${pokemonId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        golpesContainer.innerHTML = `<p style="color:red;">${data.error}</p>`;
                        return;
                    }

                    const todosGolpes = data.todos_golpes;
                    // Usamos um Set para uma verificação mais rápida (ex: O(1))
                    const golpesConhecidos = new Set(data.golpes_conhecidos.map(id => id.toString()));

                    let html = '<ul>';
                    todosGolpes.forEach(golpe => {
                        const isChecked = golpesConhecidos.has(golpe.id.toString()) ? 'checked' : '';
                        html += `
                            <li style="list-style-type: none;">
                                <label>
                                    <input type="checkbox" name="golpes[]" value="${golpe.id}" ${isChecked}>
                                    ${golpe.nome}
                                </label>
                            </li>
                        `;
                    });
                    html += '</ul>';
                    golpesContainer.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro ao buscar golpes:', error);
                    golpesContainer.innerHTML = '<p style="color:red;">Ocorreu um erro ao carregar os golpes.</p>';
                });
        });
    }
});