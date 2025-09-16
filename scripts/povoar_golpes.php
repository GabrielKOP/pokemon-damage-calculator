<?php
// scripts/povoar_golpes.php

set_time_limit(0);
ignore_user_abort(true);

// --- FUNÇÃO DE STATUS CORRIGIDA ---
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
        // Comandos para forçar o envio da saída para o navegador em tempo real
        ob_flush();
        flush();
    }
}

if (php_sapi_name() !== 'cli') {
    echo "<h1>Iniciando População da Tabela de Golpes via PokéAPI</h1>";
    echo "<p>Este processo pode demorar alguns minutos.</p>";
}

require_once dirname(__DIR__) . '/config/db.php';

// --- PASSO 1: LIMPEZA DAS TABELAS RELEVANTES ---
print_status("PASSO 1: Limpando tabelas de Golpes e suas dependências...", 'title');
mysqli_query($conexao, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conexao, "TRUNCATE TABLE `pokemon_golpes`");
mysqli_query($conexao, "TRUNCATE TABLE `golpes`");
mysqli_query($conexao, "SET FOREIGN_KEY_CHECKS = 1");
print_status("Tabelas limpas com sucesso.", 'success');

// --- PASSO 2: MAPEAR OS TIPOS EXISTENTES ---
$tipos_map_local = [];
$resultado_tipos = mysqli_query($conexao, "SELECT id, nome FROM `tipos`");
while ($tipo = mysqli_fetch_assoc($resultado_tipos)) {
    $tipos_map_local[$tipo['nome']] = $tipo['id'];
}
print_status("Mapa de tipos locais criado.", 'success');

// --- PASSO 3: BUSCAR E INSERIR GOLPES DA GERAÇÃO 1 ---
print_status("PASSO 3: Buscando e inserindo os Golpes da Geração 1...", 'title');
$gen1_data_json = @file_get_contents("https://pokeapi.co/api/v2/generation/1/");
if(!$gen1_data_json) die(print_status("Falha ao contactar a PokéAPI para buscar dados da Geração 1.", 'error'));
$gen1_data = json_decode($gen1_data_json, true);

$golpes_para_inserir = [];
$tipos_especiais = ['fire', 'water', 'grass', 'electric', 'ice', 'psychic', 'dragon', 'ghost'];

foreach ($gen1_data['moves'] as $move_entry) {
    print_status("Processando golpe: " . $move_entry['name']);
    $move_data_json = @file_get_contents($move_entry['url']);
    if(!$move_data_json) continue;
    $move_data = json_decode($move_data_json, true);

    $nome_original = $move_data['name'];
    $nome_formatado_bd = str_replace('-', ' ', $nome_original);
    $nome_para_inserir = mysqli_real_escape_string($conexao, $nome_formatado_bd);
    $poder = isset($move_data['power']) ? (int)$move_data['power'] : 0;
    $tipo_nome = $move_data['type']['name'];
    $tipo_id = isset($tipos_map_local[$tipo_nome]) ? $tipos_map_local[$tipo_nome] : null;

    if ($tipo_id) {
        $categoria = in_array($tipo_nome, $tipos_especiais) ? 'especial' : 'fisico';
        // Ordem correta das colunas: nome, tipo_id, poder, categoria
        $golpes_para_inserir[] = "('{$nome_para_inserir}', {$tipo_id}, {$poder}, '{$categoria}')";
    }
    usleep(50000);
}

// --- PASSO 4: INSERÇÃO FINAL DOS GOLPES ---
print_status("PASSO 4: Inserindo golpes na base de dados...", 'title');
if (!empty($golpes_para_inserir)) {
    $sql_insert_golpes = "INSERT INTO `golpes` (nome, tipo_id, poder, categoria) VALUES " . implode(', ', $golpes_para_inserir);
    if (mysqli_query($conexao, $sql_insert_golpes)) {
        print_status(count($golpes_para_inserir) . " golpes inseridos com sucesso.", 'success');
    } else {
        die(print_status("Erro ao inserir golpes: " . mysqli_error($conexao), 'error'));
    }
} else {
    print_status("Nenhum golpe válido foi encontrado para inserir.", "error");
}


print_status("<h2>Processo Concluído!</h2>", 'title');
mysqli_close($conexao);
?>