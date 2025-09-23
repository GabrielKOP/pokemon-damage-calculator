<?php
require_once 'config/db.php';

// VERIFICAÇÃO PRINCIPAL: Confere se a base de dados existe
if ($conexao === false) {
    die("<h1>Bem-vindo! Parece que esta é uma nova instalação.</h1><p>A base de dados 'pokemon_calculator' não foi encontrada. Por favor, execute primeiro os scripts de setup na pasta <strong>/scripts/</strong> para criar e popular a base de dados.</p><p>A ordem de execução recomendada é:<ol><li>scripts/criar_estrutura.php(cria o banco de dados)</li><li>scripts/povoar_pokemon_e_tipos.php (povoa as tabelas com os pokémon e tipos)</li><li>scripts/povoar_golpes.php (cria os golpes)</li><li>scripts/ligar_golpes_a_pokemon.php (liga os golpes aos pokémon)</li></ol></p><p>Depois, não se esqueça de executar o script para criar o utilizador administrador.</p>");
}

// Se o script continuar, a conexão foi um sucesso.
$sql_pokemon = "SELECT id, nome FROM pokemon ORDER BY id ASC";
$resultado_pokemon = mysqli_query($conexao, $sql_pokemon);

$sql_eficacia = "SELECT * FROM tipo_eficacia";
$resultado_eficacia = mysqli_query($conexao, $sql_eficacia);
$tabela_eficacia = [];
while ($linha = mysqli_fetch_assoc($resultado_eficacia)) {
    $chave = $linha['atacante_tipo_id'] . '-' . $linha['defensor_tipo_id'];
    $tabela_eficacia[$chave] = (float)$linha['multiplicador'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Dano Pokémon - Batalha</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
    <script>
        const TabelaDeEficacia = <?php echo json_encode($tabela_eficacia); ?>;
    </script>
</head>
<body>

    <div class="battle-scene">
        <div class="opponent-area">
            <div class="status-box" id="defensor_status">
                <div class="info">
                    <span id="defensor_nome">DEFENSOR</span>
                    <small>Lv50</small>
                </div>
                <div class="hp-bar-container">
                    <div class="hp-bar" id="defensor_hp_bar"></div>
                </div>
                <div class="hp-text">
                    <span id="defensor_hp_atual">--</span> / <span id="defensor_hp_max">--</span>
                </div>
            </div>
            <div class="sprite-container">
                <img id="defensor_sprite" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Sprite do Defensor">
            </div>
        </div>

        <div class="player-area">
            <div class="sprite-container">
                <img id="atacante_sprite" src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="Sprite do Atacante">
            </div>
            <div class="status-box" id="atacante_status">
                <div class="info">
                    <span id="atacante_nome">ATACANTE</span>
                    <small>Lv50</small>
                </div>
                <div class="hp-bar-container">
                    <div class="hp-bar" id="atacante_hp_bar"></div>
                </div>
                 <div class="hp-text">
                    <span id="atacante_hp_atual">--</span> / <span id="atacante_hp_max">--</span>
                </div>
            </div>
        </div>

        <div class="action-box">
            <div class="controls-container">
                <div class="selection-column">
                    <div>
                        <label for="pokemon_atacante">Atacante:</label>
                        <select name="pokemon_atacante" id="pokemon_atacante">
                             <option value="">Selecione...</option>
                             <?php mysqli_data_seek($resultado_pokemon, 0); while ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) { echo "<option value='" . $pokemon['id'] . "'>#" . $pokemon['id'] . " " . htmlspecialchars($pokemon['nome']) . "</option>"; } ?>
                        </select>
                    </div>
                    <div>
                        <label for="pokemon_defensor">Defensor:</label>
                        <select name="pokemon_defensor" id="pokemon_defensor">
                             <option value="">Selecione...</option>
                             <?php mysqli_data_seek($resultado_pokemon, 0); while ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) { echo "<option value='" . $pokemon['id'] . "'>#" . $pokemon['id'] . " " . htmlspecialchars($pokemon['nome']) . "</option>"; } ?>
                        </select>
                    </div>
                </div>
                <div class="attack-column">
                     <div id="golpes_atacante_container"></div>
                     <div id="opcoes_atacante_container">
                        <label><input type="checkbox" id="acerto_critico"> Acerto Crítico</label>
                     </div>
                </div>
            </div>
            <div class="results-container">
                <button id="calcular_btn">CALCULAR</button>
                <div id="resultado_dano">Selecione os Pokémon e um golpe para calcular o dano.</div>
            </div>
        </div>
    </div>

    <script src="public/js/script.js"></script>
</body>
</html>
<?php
if ($conexao) {
    mysqli_close($conexao);
}
?>