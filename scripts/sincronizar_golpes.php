<?php
// scripts/sincronizar_golpes.php

// Aumenta o tempo máximo de execução do script, pois ele pode demorar.
// 0 = sem limite de tempo.
set_time_limit(0);
// Garante que o script continue a ser executado mesmo que o utilizador feche o navegador.
ignore_user_abort(true);

echo "<h1>Iniciando Sincronização de Golpes...</h1>";
echo "<p>Este processo pode demorar vários minutos. Por favor, não feche esta janela.</p>";
flush(); // Envia a saída de texto para o navegador imediatamente.

require_once '../config/db.php';

// --- PASSO 1: Limpar a tabela de ligações existente ---
echo "<p>Limpando dados antigos...</p>";
flush();
mysqli_query($conexao, "TRUNCATE TABLE pokemon_golpes");

// --- PASSO 2: Mapear os nossos golpes locais ---
// Buscamos todos os nossos golpes e guardamo-los num array para acesso rápido.
// A chave será o nome do golpe formatado como na API (ex: 'swords-dance')
echo "<p>Mapeando golpes locais...</p>";
flush();
$golpes_map = [];
$resultado_golpes = mysqli_query($conexao, "SELECT id, nome FROM golpes");
while ($golpe = mysqli_fetch_assoc($resultado_golpes)) {
    // Formata o nome para ser igual ao da PokéAPI (minúsculas, espaço vira hífen)
    $nome_formatado = strtolower(str_replace(' ', '-', $golpe['nome']));
    $golpes_map[$nome_formatado] = $golpe['id'];
}

$valores_para_inserir = []; // Array para guardar os pares (pokemon_id, golpe_id)

// --- PASSO 3: Loop através de cada Pokémon para buscar dados da PokéAPI ---
for ($pokemon_id = 1; $pokemon_id <= 151; $pokemon_id++) {
    
    echo "<p>Buscando golpes para o Pokémon #{$pokemon_id}...";
    flush();

    $url = "https://pokeapi.co/api/v2/pokemon/{$pokemon_id}/";
    
    // A @ suprime warnings se a API falhar. Verificamos o resultado logo a seguir.
    $json_data = @file_get_contents($url);

    if ($json_data === false) {
        echo " <span style='color:red;'>Falhou!</span></p>";
        continue; // Pula para o próximo Pokémon
    }

    $data = json_decode($json_data, true);
    $pokemon_nome = ucfirst($data['name']);
    $golpes_encontrados = 0;

    // --- PASSO 4: Processar a resposta da API ---
    foreach ($data['moves'] as $move_data) {
        // A API retorna golpes de todas as gerações. Precisamos de filtrar.
        foreach ($move_data['version_group_details'] as $detail) {
            // Filtramos apenas para a primeira geração (Red/Blue)
            if ($detail['version_group']['name'] === 'red-blue') {
                $nome_golpe_api = $move_data['move']['name'];

                // Verificamos se este golpe existe no nosso mapa de golpes locais
                if (isset($golpes_map[$nome_golpe_api])) {
                    $golpe_id_local = $golpes_map[$nome_golpe_api];
                    // Adiciona o par (pokemon_id, golpe_id) ao nosso array de inserção
                    $valores_para_inserir[] = "({$pokemon_id}, {$golpe_id_local})";
                    $golpes_encontrados++;
                }
                break; // Encontrámos a info da Gen 1, podemos parar de procurar neste golpe
            }
        }
    }
    echo " <span style='color:green;'>Sucesso! {$golpes_encontrados} golpes encontrados para {$pokemon_nome}.</span></p>";
    flush();
    
    // Pequena pausa para não sobrecarregar a API
    usleep(250000); // 0.25 segundos
}


// --- PASSO 5: Inserir todos os dados de uma só vez no banco de dados ---
if (!empty($valores_para_inserir)) {
    echo "<h2>A inserir todas as ligações na base de dados...</h2>";
    flush();
    
    // Juntamos todos os valores numa única query para ser muito mais rápido
    $sql_insert = "INSERT INTO pokemon_golpes (pokemon_id, golpe_id) VALUES " . implode(', ', $valores_para_inserir);
    
    if (mysqli_query($conexao, $sql_insert)) {
        $total = count($valores_para_inserir);
        echo "<h2 style='color:green;'>Sincronização concluída! {$total} ligações foram criadas com sucesso.</h2>";
        echo "<p><strong>IMPORTANTE:</strong> Por favor, apague ou mova o ficheiro 'sincronizar_golpes.php' do servidor por segurança.</p>";
    } else {
        echo "<h2 style='color:red;'>Erro ao inserir os dados: " . mysqli_error($conexao) . "</h2>";
    }
} else {
    echo "<h2>Nenhuma ligação de golpes encontrada para inserir.</h2>";
}

mysqli_close($conexao);

?>