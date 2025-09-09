// public/js/script.js
document.addEventListener('DOMContentLoaded', () => {
    // Seletores de elementos
    const seletorAtacante = document.getElementById('pokemon_atacante');
    const seletorDefensor = document.getElementById('pokemon_defensor');
    const detailsAtacante = document.getElementById('details_atacante');
    const detailsDefensor = document.getElementById('details_defensor');
    const golpesContainer = document.getElementById('golpes_atacante_container');
    const calcularBtn = document.getElementById('calcular_btn');
    const resultadoDano = document.getElementById('resultado_dano');

    // Mapeamento de stats para facilitar o acesso posterior
    let statsAtacante = {};
    let statsDefensor = {};

    // Ouvintes de evento
    seletorAtacante.addEventListener('change', () => {
        const pokemonId = seletorAtacante.value;
        updatePokemonInfo(pokemonId, detailsAtacante, 'atacante');
    });

    seletorDefensor.addEventListener('change', () => {
        const pokemonId = seletorDefensor.value;
        updatePokemonInfo(pokemonId, detailsDefensor, 'defensor');
    });

    calcularBtn.addEventListener('click', calcularDano);

    // Função para buscar dados e atualizar a UI
    function updatePokemonInfo(pokemonId, detailsElement, tipo) {
        golpesContainer.innerHTML = ''; // Limpa os golpes ao trocar de Pokémon
        resultadoDano.innerHTML = '';   // Limpa o resultado anterior

        if (!pokemonId) {
            detailsElement.innerHTML = '';
            if (tipo === 'atacante') statsAtacante = {};
            else statsDefensor = {};
            return;
        }

        fetch(`get_pokemon_details.php?id=${pokemonId}`)
            .then(response => response.json())
            .then(data => {
                // Guarda os stats
                if (tipo === 'atacante') statsAtacante = data;
                else statsDefensor = data;

                // Mostra os stats
                const tipo2 = data.tipo2_nome ? ` / ${data.tipo2_nome}` : '';
                detailsElement.innerHTML = `<h4>Stats</h4> <p><strong>Tipo(s):</strong> ${data.tipo1_nome}${tipo2}</p> <p><strong>HP:</strong> ${data.hp}</p> <p><strong>Ataque:</strong> ${data.ataque}</p> <p><strong>Defesa:</strong> ${data.defesa}</p> <p><strong>Especial:</strong> ${data.especial}</p> <p><strong>Velocidade:</strong> ${data.velocidade}</p>`;

                // Se for o atacante, mostra os golpes
                if (tipo === 'atacante' && data.golpes && data.golpes.length > 0) {
                    let golpesHtml = '<label for="golpe_selecionado">Golpe:</label><select id="golpe_selecionado">';
                    data.golpes.forEach(golpe => {
                        // Usamos atributos data-* para guardar informações extras no HTML
                        golpesHtml += `<option value="${golpe.id}" data-poder="${golpe.poder}" data-categoria="${golpe.categoria}" data-tipo="${golpe.tipo_nome}">${golpe.nome} (${golpe.tipo_nome})</option>`;
                    });
                    golpesHtml += '</select>';
                    golpesContainer.innerHTML = golpesHtml;
                }
            })
            .catch(error => console.error('Erro:', error));
    }
    
    // Função para calcular o dano
    function calcularDano() {
        // Validação básica
        if (!statsAtacante.id || !statsDefensor.id || !document.getElementById('golpe_selecionado')) {
            resultadoDano.innerHTML = 'Selecione o atacante, o defensor e um golpe.';
            return;
        }

        const levelAtacante = 50; // Nível fixo para simplificar

        const seletorGolpe = document.getElementById('golpe_selecionado');
        const golpeSelecionadoOption = seletorGolpe.options[seletorGolpe.selectedIndex];
        
        // Pega os dados do golpe dos atributos data-*
        const poderGolpe = parseInt(golpeSelecionadoOption.dataset.poder);
        const categoriaGolpe = golpeSelecionadoOption.dataset.categoria;

        // Determina qual stat de ataque/defesa usar baseado na categoria do golpe
        let ataqueUsado, defesaUsada;
        if (categoriaGolpe === 'fisico') {
            ataqueUsado = parseInt(statsAtacante.ataque);
            defesaUsada = parseInt(statsDefensor.defesa);
        } else { // 'especial'
            ataqueUsado = parseInt(statsAtacante.especial);
            defesaUsada = parseInt(statsDefensor.especial);
        }

        // --- Fórmula de Dano da Geração 1 (simplificada) ---
        // Dano = (((((2 * Level / 5) + 2) * Poder * Ataque) / Defesa) / 50) + 2
        let dano = Math.floor((((((2 * levelAtacante / 5) + 2) * poderGolpe * ataqueUsado) / defesaUsada) / 50) + 2);

        // Por enquanto, vamos ignorar modificadores como STAB e Efetividade de Tipo
        resultadoDano.innerHTML = `Dano Base Estimado: <strong>${dano}</strong>`;
    }
});