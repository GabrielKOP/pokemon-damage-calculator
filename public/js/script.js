console.log("O script.js versão 2.0 foi carregado e está a ser executado!");

document.addEventListener('DOMContentLoaded', () => {
    // Seletores de elementos
    const seletorAtacante = document.getElementById('pokemon_atacante');
    const seletorDefensor = document.getElementById('pokemon_defensor');
    const golpesContainer = document.getElementById('golpes_atacante_container');
    const calcularBtn = document.getElementById('calcular_btn');
    const resultadoDano = document.getElementById('resultado_dano');
    const criticoCheckbox = document.getElementById('acerto_critico');

    // Mapeamento de stats
    let statsAtacante = {};
    let statsDefensor = {};

    // Ouvintes de evento
    seletorAtacante.addEventListener('change', () => {
        updatePokemonInfo(seletorAtacante.value, 'atacante');
    });
    seletorDefensor.addEventListener('change', () => {
        updatePokemonInfo(seletorDefensor.value, 'defensor');
    });
    calcularBtn.addEventListener('click', calcularDano);

    function updatePokemonInfo(pokemonId, tipo) {
        if (!pokemonId) return;

        fetch(`get_pokemon_details.php?id=${pokemonId}`)
            .then(response => response.json())
            .then(data => {
                const spriteElement = document.getElementById(`${tipo}_sprite`);
                const nomeElement = document.getElementById(`${tipo}_nome`);
                const hpAtualElement = document.getElementById(`${tipo}_hp_atual`);
                const hpMaxElement = document.getElementById(`${tipo}_hp_max`);
                const hpBarElement = document.getElementById(`${tipo}_hp_bar`);

                if (tipo === 'atacante') {
                    statsAtacante = data;
                    spriteElement.src = `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/back/${data.id}.png`;
                    golpesContainer.innerHTML = '';
                    if (data.golpes && data.golpes.length > 0) {
                        let golpesHtml = '<label for="golpe_selecionado">Golpe:</label><select id="golpe_selecionado">';
                        data.golpes.forEach(golpe => {
                            golpesHtml += `<option value="${golpe.id}" data-poder="${golpe.poder}" data-categoria="${golpe.categoria}" data-tipo-id="${golpe.tipo_id}">${golpe.nome}</option>`;
                        });
                        golpesHtml += '</select>';
                        golpesContainer.innerHTML = golpesHtml;
                    }
                } else {
                    statsDefensor = data;
                    spriteElement.src = `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/${data.id}.png`;
                }

                nomeElement.textContent = data.nome.toUpperCase();
                hpAtualElement.textContent = data.hp;
                hpMaxElement.textContent = data.hp;
                hpBarElement.style.width = '100%';
                updateHpBarColor(hpBarElement, 100);
            })
            .catch(error => console.error('Erro:', error));
    }

    function updateHpBarColor(barElement, hpPercent) {
        barElement.classList.remove('red', 'yellow');
        if (hpPercent <= 20) {
            barElement.classList.add('red');
        } else if (hpPercent <= 50) {
            barElement.classList.add('yellow');
        }
    }
    
    function calcularDano() {
        if (!statsAtacante.id || !statsDefensor.id || !document.getElementById('golpe_selecionado')) {
            resultadoDano.innerHTML = 'Selecione atacante, defensor e um golpe.';
            return;
        }

        const levelAtacante = 50;
        const isCritico = criticoCheckbox.checked;

        const seletorGolpe = document.getElementById('golpe_selecionado');
        const opt = seletorGolpe.options[seletorGolpe.selectedIndex];
        
        const poderGolpe = parseInt(opt.dataset.poder);

        // --- VERIFICAÇÃO ADICIONADA ---
        // Se o poder do golpe for 0, ele não causa dano.
        if (poderGolpe === 0) {
            resultadoDano.innerHTML = `O golpe ${opt.text} não causa dano direto.`;
            return; // Sai da função imediatamente.
        }
        // --- FIM DA VERIFICAÇÃO ---
        
        const categoriaGolpe = opt.dataset.categoria;
        const tipoGolpeId = opt.dataset.tipoId;
        const tipo1AtacanteId = statsAtacante.tipo1_id, tipo2AtacanteId = statsAtacante.tipo2_id;
        let ataqueUsado, defesaUsada;
        if (categoriaGolpe === 'fisico') {
            ataqueUsado = parseInt(statsAtacante.ataque);
            defesaUsada = parseInt(statsDefensor.defesa);
        } else {
            ataqueUsado = parseInt(statsAtacante.especial);
            defesaUsada = parseInt(statsDefensor.especial);
        }
        const levelCalculo = isCritico ? levelAtacante * 2 : levelAtacante;
        const danoBase = Math.floor((((((2 * levelCalculo / 5) + 2) * poderGolpe * ataqueUsado) / defesaUsada) / 50) + 2);
        let multiplicadorStab = 1.0;
        if (tipoGolpeId == tipo1AtacanteId || tipoGolpeId == tipo2AtacanteId) multiplicadorStab = 1.5;
        const tipo1DefensorId = statsDefensor.tipo1_id, tipo2DefensorId = statsDefensor.tipo2_id;
        const chave1 = `${tipoGolpeId}-${tipo1DefensorId}`;
        let multEficacia1 = TabelaDeEficacia[chave1] !== undefined ? TabelaDeEficacia[chave1] : 1.0;
        let multEficacia2 = 1.0;
        if (tipo2DefensorId) {
            const chave2 = `${tipoGolpeId}-${tipo2DefensorId}`;
            multEficacia2 = TabelaDeEficacia[chave2] !== undefined ? TabelaDeEficacia[chave2] : 1.0;
        }
        const multiplicadorEficaciaTotal = multEficacia1 * multEficacia2;
        const danoModificado = Math.floor(danoBase * multiplicadorStab * multiplicadorEficaciaTotal);
        const danoMinimo = Math.floor(danoModificado * 0.85);
        const danoMaximo = danoModificado;
        
        const danoMedio = Math.floor((danoMinimo + danoMaximo) / 2);
        const hpMaxDefensor = parseInt(statsDefensor.hp);
        const hpAtualElement = document.getElementById('defensor_hp_atual');
        const hpBarElement = document.getElementById('defensor_hp_bar');
        const hpAnterior = parseInt(hpAtualElement.textContent);
        
        const hpRestante = Math.max(0, hpAnterior - danoMedio);
        const hpPercentRestante = (hpRestante / hpMaxDefensor) * 100;

        hpAtualElement.textContent = hpRestante;
        hpBarElement.style.width = `${hpPercentRestante}%`;
        updateHpBarColor(hpBarElement, hpPercentRestante);
        
        let mensagemEficacia = '';
        if (multiplicadorEficaciaTotal >= 2.0) mensagemEficacia = ' É super efetivo!';
        else if (multiplicadorEficaciaTotal > 0 && multiplicadorEficaciaTotal < 1.0) mensagemEficacia = ' Não é muito efetivo...';
        else if (multiplicadorEficaciaTotal === 0) mensagemEficacia = ' Não tem efeito!';
        const criticoTexto = isCritico ? " Um acerto crítico!" : "";

        resultadoDano.innerHTML = `${statsAtacante.nome.toUpperCase()} usou ${opt.text}! Dano: ${danoMinimo}-${danoMaximo}.${criticoTexto}${mensagemEficacia}`;
    }
});