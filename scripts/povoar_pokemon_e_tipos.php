<?php
// scripts/povoar_pokemon_e_tipos.php (VERSÃO COMPLETA E ATUALIZADA)

// --- CONFIGURAÇÕES INICIAIS ---
set_time_limit(0);
ignore_user_abort(true);

function print_status($message, $type = 'info') {
    $is_cli = php_sapi_name() === 'cli';
    if ($is_cli) {
        echo strip_tags($message) . "\n";
    } else {
        $color = '#333';
        if ($type === 'success') $color = 'green';
        if ($type === 'error') $color = 'red';
        if ($type === 'title') $color = 'blue';
        echo "<p style='color: {$color}; margin: 2px 0; font-family: monospace;'>{$message}</p>";
        ob_flush();
        flush();
    }
}

if (php_sapi_name() !== 'cli') {
    echo "<h1>Iniciando População de Pokémon, Tipos e Regras de Eficácia</h1>";
    echo "<p>Este processo pode demorar alguns minutos.</p>";
}

require_once dirname(__DIR__) . '/config/db.php';
if ($conexao === false) {
    die(print_status("Erro: A base de dados não foi encontrada. Por favor, execute primeiro o script 'criar_estrutura.php'.", "error"));
}


// --- PASSO 1: LIMPEZA DAS TABELAS RELEVANTES ---
print_status("PASSO 1: Limpando dados antigos de Pokémon e Tipos...", 'title');
mysqli_query($conexao, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conexao, "TRUNCATE TABLE `pokemon_golpes`");
mysqli_query($conexao, "TRUNCATE TABLE `tipo_eficacia`");
mysqli_query($conexao, "TRUNCATE TABLE `pokemon`");
mysqli_query($conexao, "TRUNCATE TABLE `tipos`");
mysqli_query($conexao, "SET FOREIGN_KEY_CHECKS = 1");
print_status("Tabelas limpas com sucesso.", 'success');

$tipos_map_local = [];

// --- PASSO 2: BUSCAR E INSERIR TODOS OS TIPOS ---
print_status("PASSO 2: Buscando e inserindo TODOS os tipos existentes...", 'title');
$all_types_data_json = @file_get_contents("https://pokeapi.co/api/v2/type/");
if(!$all_types_data_json) die(print_status("Falha ao contactar a PokéAPI para buscar a lista de tipos.", 'error'));
$all_types_data = json_decode($all_types_data_json, true);
$tipos_para_inserir = [];
foreach ($all_types_data['results'] as $type_entry) {
    if ($type_entry['name'] !== 'shadow' && $type_entry['name'] !== 'unknown') {
        $tipos_para_inserir[] = "('" . mysqli_real_escape_string($conexao, $type_entry['name']) . "')";
    }
}
$sql_insert_tipos = "INSERT INTO `tipos` (nome) VALUES " . implode(', ', $tipos_para_inserir);
mysqli_query($conexao, $sql_insert_tipos);

$resultado_tipos = mysqli_query($conexao, "SELECT id, nome FROM `tipos`");
while ($tipo = mysqli_fetch_assoc($resultado_tipos)) {
    $tipos_map_local[$tipo['nome']] = $tipo['id'];
}
print_status(count($tipos_map_local) . " tipos inseridos e mapeados.", 'success');


// --- PASSO 3: INSERINDO A TABELA DE EFICÁCIA DE TIPOS ---
print_status("PASSO 3: Inserindo a Tabela de Efetividade de Tipos (Geração 1)...", 'title');
$sql_eficacia = "
INSERT INTO `tipo_eficacia` (`atacante_tipo_id`, `defensor_tipo_id`, `multiplicador`) VALUES
(1, 13, 0.5), (1, 14, 0.0), (2, 2, 0.5), (2, 3, 0.5), (2, 5, 2.0), (2, 6, 2.0), (2, 12, 2.0),
(2, 13, 0.5), (2, 15, 0.5), (3, 2, 2.0), (3, 3, 0.5), (3, 5, 0.5), (3, 9, 2.0), (3, 13, 2.0),
(3, 15, 0.5), (4, 3, 2.0), (4, 4, 0.5), (4, 5, 0.5), (4, 9, 0.0), (4, 10, 2.0), (4, 15, 0.5),
(5, 2, 0.5), (5, 3, 2.0), (5, 5, 0.5), (5, 8, 0.5), (5, 9, 2.0), (5, 10, 0.5), (5, 12, 0.5),
(5, 13, 2.0), (5, 15, 0.5), (6, 3, 0.5), (6, 5, 2.0), (6, 6, 0.5), (6, 9, 2.0), (6, 10, 2.0),
(6, 15, 2.0), (7, 1, 2.0), (7, 6, 2.0), (7, 8, 0.5), (7, 10, 0.5), (7, 11, 0.5), (7, 12, 0.5),
(7, 13, 2.0), (7, 14, 0.0), (8, 5, 2.0), (8, 8, 0.5), (8, 9, 0.5), (8, 12, 2.0), (8, 13, 0.5),
(8, 14, 0.5), (9, 2, 2.0), (9, 4, 2.0), (9, 5, 0.5), (9, 8, 2.0), (9, 10, 0.0), (9, 12, 0.5),
(9, 13, 2.0), (10, 4, 0.5), (10, 5, 2.0), (10, 7, 2.0), (10, 12, 2.0), (10, 13, 0.5),
(11, 7, 2.0), (11, 8, 2.0), (11, 11, 0.5), (12, 2, 0.5), (12, 5, 2.0), (12, 7, 0.5),
(12, 8, 2.0), (12, 10, 0.5), (12, 11, 2.0), (12, 14, 0.5), (13, 2, 2.0), (13, 6, 2.0),
(13, 7, 0.5), (13, 9, 0.5), (13, 10, 2.0), (13, 12, 2.0), (14, 1, 0.0), (14, 11, 0.0),
(14, 14, 2.0), (15, 15, 2.0);
";
if (mysqli_query($conexao, $sql_eficacia)) {
    print_status("Tabela de eficácia populada com sucesso.", 'success');
} else {
    die(print_status("Erro ao popular a tabela de eficácia: " . mysqli_error($conexao), 'error'));
}


// --- PASSO 4: BUSCAR E INSERIR POKÉMON ---
print_status("PASSO 4: Buscando e inserindo os 151 Pokémon...", 'title');
$pokemon_para_inserir = [];

for ($pokemon_id = 1; $pokemon_id <= 151; $pokemon_id++) {
    print_status("Processando Pokémon #{$pokemon_id}...", 'info');
    $pokemon_data_json = @file_get_contents("https://pokeapi.co/api/v2/pokemon/{$pokemon_id}/");
    if(!$pokemon_data_json) {
        print_status("Falha ao buscar dados para Pokémon #{$pokemon_id}", 'error');
        continue;
    }
    $pokemon_data = json_decode($pokemon_data_json, true);
    
    $nome = mysqli_real_escape_string($conexao, ucfirst($pokemon_data['name']));
    $hp = 0; $ataque = 0; $defesa = 0; $especial = 0; $velocidade = 0;
    foreach ($pokemon_data['stats'] as $stat) {
        switch ($stat['stat']['name']) {
            case 'hp': $hp = $stat['base_stat']; break;
            case 'attack': $ataque = $stat['base_stat']; break;
            case 'defense': $defesa = $stat['base_stat']; break;
            case 'special-attack': $especial = $stat['base_stat']; break;
            case 'speed': $velocidade = $stat['base_stat']; break;
        }
    }

    $tipos_atuais = $pokemon_data['types'];
    
    $tipo1_id = 'NULL';
    $tipo2_id = 'NULL';

    if (isset($tipos_atuais[0])) {
        $tipo1_nome = $tipos_atuais[0]['type']['name'];
        if (isset($tipos_map_local[$tipo1_nome])) {
            $tipo1_id = $tipos_map_local[$tipo1_nome];
        }
    }
    if (isset($tipos_atuais[1])) {
        $tipo2_nome = $tipos_atuais[1]['type']['name'];
        if (isset($tipos_map_local[$tipo2_nome])) {
            $tipo2_id = $tipos_map_local[$tipo2_nome];
        }
    }
    
    if ($tipo1_id === 'NULL') {
        print_status("AVISO: Pokémon #{$pokemon_id} ({$nome}) ignorado por ter um tipo primário inválido.", 'error');
        continue; 
    }
    
    $pokemon_para_inserir[] = "({$pokemon_id}, '{$nome}', {$tipo1_id}, {$tipo2_id}, {$hp}, {$ataque}, {$defesa}, {$especial}, {$velocidade})";

    usleep(50000);
}

// --- PASSO 5: INSERÇÃO FINAL DOS POKÉMON ---
print_status("PASSO 5: Inserindo Pokémon na base de dados...", 'title');
if (!empty($pokemon_para_inserir)) {
    $sql_insert_pokemon = "INSERT INTO `pokemon` (id, nome, tipo1_id, tipo2_id, hp, ataque, defesa, especial, velocidade) VALUES " . implode(', ', $pokemon_para_inserir);
    if(mysqli_query($conexao, $sql_insert_pokemon)){
        print_status(count($pokemon_para_inserir) . " Pokémon inseridos com sucesso.", 'success');
    } else {
        print_status("Erro ao inserir Pokémon: " . mysqli_error($conexao), 'error');
    }
} else {
    print_status("Nenhum Pokémon foi preparado para inserção.", "error");
}


print_status("<h2>Processo Concluído!</h2>", 'title');
print_status("<strong>IMPORTANTE:</strong> Por favor, apague ou mova este ficheiro do servidor por segurança.", 'info');

mysqli_close($conexao);
?>