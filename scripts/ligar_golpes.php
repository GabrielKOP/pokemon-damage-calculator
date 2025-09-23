<?php
// scripts/ligar_golpes_a_pokemon.php

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
    echo "<h1>Iniciando Ligação de Golpes aos Pokémon</h1>";
    echo "<p>Este processo pode demorar vários minutos.</p>";
}

require_once dirname(__DIR__) . '/config/db.php';

// --- PASSO 1: LIMPAR LIGAÇÕES ANTIGAS ---
print_status("PASSO 1: Limpando ligações antigas...", 'title');
mysqli_query($conexao, "TRUNCATE TABLE `pokemon_golpes`");
print_status("Tabela de ligações limpa com sucesso.", 'success');

// --- PASSO 2: MAPEAR GOLPES LOCAIS ---
print_status("PASSO 2: Mapeando golpes locais...", 'title');
$golpes_map_local = [];
$resultado_golpes = mysqli_query($conexao, "SELECT id, nome FROM `golpes`");
while ($golpe = mysqli_fetch_assoc($resultado_golpes)) {
    $nome_chave_api = strtolower(str_replace(' ', '-', $golpe['nome']));
    $golpes_map_local[$nome_chave_api] = $golpe['id'];
}
print_status(count($golpes_map_local) . " golpes mapeados.", 'success');

// --- PASSO 3: BUSCAR POKÉMON E CRIAR LIGAÇÕES ---
print_status("PASSO 3: Buscando dados da PokéAPI e preparando ligações...", 'title');
$links_para_inserir = [];

for ($pokemon_id = 1; $pokemon_id <= 151; $pokemon_id++) {
    print_status("Processando Pokémon #{$pokemon_id}..."); // Esta mensagem agora aparecerá em tempo real
    $pokemon_data_json = @file_get_contents("https://pokeapi.co/api/v2/pokemon/{$pokemon_id}/");
    if(!$pokemon_data_json) continue;
    $pokemon_data = json_decode($pokemon_data_json, true);

    foreach ($pokemon_data['moves'] as $move_data) {
        foreach ($move_data['version_group_details'] as $detail) {
            if ($detail['version_group']['name'] === 'red-blue') {
                $nome_golpe_api = $move_data['move']['name'];
                if (isset($golpes_map_local[$nome_golpe_api])) {
                    $golpe_id_local = $golpes_map_local[$nome_golpe_api];
                    // Usamos array_unique no final para evitar duplicatas, mas podemos adicionar aqui
                    $link = "({$pokemon_id}, {$golpe_id_local})";
                    if(!in_array($link, $links_para_inserir)){
                        $links_para_inserir[] = $link;
                    }
                }
                break;
            }
        }
    }
    usleep(50000); // Pausa para não sobrecarregar a API
}

// --- PASSO 4: INSERÇÃO FINAL DAS LIGAÇÕES ---
print_status("PASSO 4: Inserindo ligações na base de dados...", 'title');
if (!empty($links_para_inserir)) {
    $sql_insert_links = "INSERT INTO `pokemon_golpes` (pokemon_id, golpe_id) VALUES " . implode(', ', $links_para_inserir);
    if(mysqli_query($conexao, $sql_insert_links)){
        print_status(count($links_para_inserir) . " ligações de golpes inseridas com sucesso.", 'success');
    } else {
        die(print_status("Erro ao inserir ligações: " . mysqli_error($conexao), 'error'));
    }
} else {
    print_status("Nenhuma ligação encontrada para inserir.", 'error');
}

print_status("<h2>Processo Concluído!</h2>", 'title');
mysqli_close($conexao);
?>