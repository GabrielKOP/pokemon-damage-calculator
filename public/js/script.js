document.addEventListener('DOMContentLoaded', () => {
    // Seletores de elementos (Adicionamos a checkbox)
    const seletorAtacante = document.getElementById('pokemon_atacante');
    const seletorDefensor = document.getElementById('pokemon_defensor');
    const detailsAtacante = document.getElementById('details_atacante');
    const detailsDefensor = document.getElementById('details_defensor');
    const golpesContainer = document.getElementById('golpes_atacante_container');
    const calcularBtn = document.getElementById('calcular_btn');
    const resultadoDano = document.getElementById('resultado_dano');
    const criticoCheckbox = document.getElementById('acerto_critico');

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

    // Função para buscar dados e atualizar a UI (inalterada)
    function updatePokemonInfo(pokemonId, detailsElement, tipo) {
        golpesContainer.innerHTML = '';
        resultadoDano.innerHTML = '';

        if (!pokemonId) {
            detailsElement.innerHTML = '';
            if (tipo === 'atacante') statsAtacante = {};
            else statsDefensor = {};
            return;
        }

        fetch(`get_pokemon_details.php?id=${pokemonId}`)
            .then(response => response.json())
            .then(data => {
                if (tipo === 'atacante') statsAtacante = data;
                else statsDefensor = data;

                const tipo2 = data.tipo2_nome ? ` / ${data.tipo2_nome}` : '';
                detailsElement.innerHTML = `<h4>Stats</h4> <p><strong>Tipo(s):</strong> ${data.tipo1_nome}${tipo2}</p> <p><strong>HP:</strong> ${data.hp}</p> <p><strong>Ataque:</strong> ${data.ataque}</p> <p><strong>Defesa:</strong> ${data.defesa}</p> <p><strong>Especial:</strong> ${data.especial}</p> <p><strong>Velocidade:</strong> ${data.velocidade}</p>`;

                if (tipo === 'atacante' && data.golpes && data.golpes.length > 0) {
                    let golpesHtml = '<label for="golpe_selecionado">Golpe:</label><select id="golpe_selecionado">';
                    data.golpes.forEach(golpe => {
                        golpesHtml += `<option value="${golpe.id}" data-poder="${golpe.poder}" data-categoria="${golpe.categoria}" data-tipo-id="${golpe.tipo_id}">${golpe.nome} (${golpe.tipo_nome})</option>`;
                    });
                    golpesHtml += '</select>';
                    golpesContainer.innerHTML = golpesHtml;
                }
            })
            .catch(error => console.error('Erro:', error));
    }
    
    // Função para calcular o dano (Versão ATUALIZADA)
    function calcularDano() {
        if (!statsAtacante.id || !statsDefensor.id || !document.getElementById('golpe_selecionado')) {
            resultadoDano.innerHTML = 'Selecione o atacante, o defensor e um golpe.';
            return;
        }

        const levelAtacante = 50;
        const isCritico = criticoCheckbox.checked; // Verifica se a checkbox está marcada

        const seletorGolpe = document.getElementById('golpe_selecionado');
        const golpeSelecionadoOption = seletorGolpe.options[seletorGolpe.selectedIndex];
        
        const poderGolpe = parseInt(golpeSelecionadoOption.dataset.poder);
        const categoriaGolpe = golpeSelecionadoOption.dataset.categoria;
        const tipoGolpeId = golpeSelecionadoOption.dataset.tipoId;
        const tipo1AtacanteId = statsAtacante.tipo1_id;
        const tipo2AtacanteId = statsAtacante.tipo2_id;

        let ataqueUsado, defesaUsada;
        // Com um acerto crítico na G1, os stats de Ataque e Defesa do oponente são ignorados.
        // Usa-se o stat base do Pokémon, não o stat modificado em batalha.
        // Para a nossa calculadora, o efeito é o mesmo, mas a lógica é importante.
        if (categoriaGolpe === 'fisico') {
            ataqueUsado = parseInt(statsAtacante.ataque);
            defesaUsada = parseInt(statsDefensor.defesa);
        } else {
            ataqueUsado = parseInt(statsAtacante.especial);
            defesaUsada = parseInt(statsDefensor.especial);
        }

        // --- Fórmula de Dano da Geração 1 ---
        // Na G1, um acerto crítico dobra o Nível do atacante na fórmula.
        const levelCalculo = isCritico ? levelAtacante * 2 : levelAtacante;
        
        const danoBase = Math.floor((((((2 * levelCalculo / 5) + 2) * poderGolpe * ataqueUsado) / defesaUsada) / 50) + 2);

        // STAB (Same-Type Attack Bonus)
        let multiplicadorStab = 1.0;
        if (tipoGolpeId == tipo1AtacanteId || tipoGolpeId == tipo2AtacanteId) {
            multiplicadorStab = 1.5;
        }

        // Efetividade de Tipo
        const tipo1DefensorId = statsDefensor.tipo1_id;
        const tipo2DefensorId = statsDefensor.tipo2_id;
        
        const chave1 = `${tipoGolpeId}-${tipo1DefensorId}`;
        let multEficacia1 = TabelaDeEficacia[chave1] !== undefined ? TabelaDeEficacia[chave1] : 1.0;

        let multEficacia2 = 1.0;
        if (tipo2DefensorId) {
            const chave2 = `${tipoGolpeId}-${tipo2DefensorId}`;
            multEficacia2 = TabelaDeEficacia[chave2] !== undefined ? TabelaDeEficacia[chave2] : 1.0;
        }
        const multiplicadorEficaciaTotal = multEficacia1 * multEficacia2;
        
        const danoModificado = Math.floor(danoBase * multiplicadorStab * multiplicadorEficaciaTotal);
        
        // NOVO: Aplicar a variação de dano (entre 85% e 100%)
        const danoMinimo = Math.floor(danoModificado * 0.85);
        const danoMaximo = danoModificado; // O máximo é o próprio valor calculado
        
        // Mensagem de resultado
        let mensagemEficacia = '';
        if (multiplicadorEficaciaTotal >= 2.0) mensagemEficacia = ' (É super efetivo!)';
        else if (multiplicadorEficaciaTotal > 0 && multiplicadorEficaciaTotal < 1.0) mensagemEficacia = ' (Não é muito efetivo...)';
        else if (multiplicadorEficaciaTotal === 0) mensagemEficacia = ' (Não tem efeito!)';

        const criticoTexto = isCritico ? " <strong>Um acerto crítico!</strong>" : "";

        if (danoMinimo === 0 && danoMaximo === 0) {
             resultadoDano.innerHTML = `Não causa dano.${mensagemEficacia}`;
        } else {
             resultadoDano.innerHTML = `Dano Estimado: <strong>${danoMinimo} - ${danoMaximo}</strong>${criticoTexto}${mensagemEficacia}`;
        }
    }
});