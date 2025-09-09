<?php
require_once 'config/db.php';

// Query para a lista de Pokémon
$sql_pokemon = "SELECT id, nome FROM pokemon ORDER BY nome ASC";
$resultado_pokemon = mysqli_query($conexao, $sql_pokemon);

// Query para buscar TODA a tabela de efetividade de tipos
$sql_eficacia = "SELECT * FROM tipo_eficacia";
$resultado_eficacia = mysqli_query($conexao, $sql_eficacia);

$tabela_eficacia = [];
while ($linha = mysqli_fetch_assoc($resultado_eficacia)) {
    // Organiza os dados numa estrutura fácil de usar em JS: { '1-13': 0.5 }
    $chave = $linha['atacante_tipo_id'] . '-' . $linha['defensor_tipo_id'];
    $tabela_eficacia[$chave] = (float)$linha['multiplicador'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Dano Pokémon - G1</title>
    <link rel="stylesheet" href="public/css/style.css">
    
    <script>
        const TabelaDeEficacia = <?php echo json_encode($tabela_eficacia); ?>;
    </script>
</head>
<body>

    <header>
        <h1>Calculadora de Dano Pokémon (Geração 1)</h1>
    </header>

    <main class="calculadora-container">
        
        <section class="coluna-pokemon" id="coluna_atacante">
            <h2>Atacante</h2>
            
            <div>
                <label for="pokemon_atacante">Pokémon:</label>
                <select name="pokemon_atacante" id="pokemon_atacante" class="seletor-pokemon">
                    <option value="">Selecione um Pokémon</option>
                    <?php
                        while ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) {
                            echo "<option value='" . $pokemon['id'] . "'>" . htmlspecialchars($pokemon['nome']) . "</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div class="pokemon-details" id="details_atacante"></div>
            
            <div id="golpes_atacante_container"></div>

            <div class="opcoes-container" id="opcoes_atacante_container">
                <label for="acerto_critico">
                    <input type="checkbox" id="acerto_critico" name="acerto_critico"> Forçar Acerto Crítico
                </label>
            </div>
        </section>

        <section class="coluna-pokemon" id="coluna_defensor">
            <h2>Defensor</h2>
            
            <div>
                <label for="pokemon_defensor">Pokémon:</label>
                <select name="pokemon_defensor" id="pokemon_defensor" class="seletor-pokemon">
                    <option value="">Selecione um Pokémon</option>
                    <?php
                        mysqli_data_seek($resultado_pokemon, 0);
                        while ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) {
                            echo "<option value='" . $pokemon['id'] . "'>" . htmlspecialchars($pokemon['nome']) . "</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="pokemon-details" id="details_defensor"></div>
        </section>

    </main>
    
    <div class="resultado-container">
        <button id="calcular_btn">Calcular Dano</button>
        <div id="resultado_dano"></div>
    </div>
    
    <script src="public/js/script.js"></script>

</body>
</html>
<?php
mysqli_close($conexao);
?>