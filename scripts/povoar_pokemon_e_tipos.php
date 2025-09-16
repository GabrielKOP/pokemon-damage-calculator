<?php
// scripts/povoar_pokemon_e_tipos.php (VERSÃO TUDO-EM-UM: CRIA E POVOA)

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
    echo "<h1>Iniciando Setup Completo do Banco de Dados via PokéAPI</h1>";
    echo "<p>Este processo pode demorar vários minutos. Por favor, seja paciente.</p>";
}

require_once dirname(__DIR__) . '/config/db.php';

// --- PASSO 1 (NOVO): (RE)CRIAÇÃO DA ESTRUTURA DO BANCO DE DADOS ---
print_status("PASSO 1: Verificando e (Re)Criando a estrutura das tabelas...", 'title');

$sql_schema = "
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `pokemon_golpes`;
DROP TABLE IF EXISTS `tipo_eficacia`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `pokemon`;
DROP TABLE IF EXISTS `golpes`;
DROP TABLE IF EXISTS `tipos`;

CREATE TABLE `tipos` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE `pokemon` (
  `id` INT PRIMARY KEY,
  `nome` VARCHAR(100) NOT NULL,
  `tipo1_id` INT NOT NULL,
  `tipo2_id` INT NULL,
  `hp` INT NOT NULL,
  `ataque` INT NOT NULL,
  `defesa` INT NOT NULL,
  `especial` INT NOT NULL,
  `velocidade` INT NOT NULL,
  FOREIGN KEY (`tipo1_id`) REFERENCES `tipos`(`id`),
  FOREIGN KEY (`tipo2_id`) REFERENCES `tipos`(`id`)
);

CREATE TABLE `golpes` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL,
  `poder` INT NOT NULL,
  `tipo_id` INT NOT NULL,
  `categoria` ENUM('fisico', 'especial') NOT NULL,
  FOREIGN KEY (`tipo_id`) REFERENCES `tipos`(`id`)
);

CREATE TABLE `usuarios` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `senha` VARCHAR(255) NOT NULL,
  `perfil` ENUM('admin', 'comum') NOT NULL DEFAULT 'comum'
);

CREATE TABLE `pokemon_golpes` (
  `pokemon_id` INT NOT NULL,
  `golpe_id` INT NOT NULL,
  PRIMARY KEY (`pokemon_id`, `golpe_id`),
  FOREIGN KEY (`pokemon_id`) REFERENCES `pokemon`(`id`),
  FOREIGN KEY (`golpe_id`) REFERENCES `golpes`(`id`)
);

CREATE TABLE `tipo_eficacia` (
  `atacante_tipo_id` INT NOT NULL,
  `defensor_tipo_id` INT NOT NULL,
  `multiplicador` DECIMAL(2,1) NOT NULL,
  PRIMARY KEY (`atacante_tipo_id`, `defensor_tipo_id`),
  FOREIGN KEY (`atacante_tipo_id`) REFERENCES `tipos`(`id`),
  FOREIGN KEY (`defensor_tipo_id`) REFERENCES `tipos`(`id`)
);

SET FOREIGN_KEY_CHECKS = 1;
";

// Como estamos a executar vários comandos SQL de uma só vez, usamos mysqli_multi_query
if (mysqli_multi_query($conexao, $sql_schema)) {
    // É preciso limpar os resultados de multi-query antes de continuar
    while (mysqli_next_result($conexao)) {;}
    print_status("Estrutura do banco de dados criada com sucesso.", 'success');
} else {
    die(print_status("Erro ao criar a estrutura do banco de dados: " . mysqli_error($conexao), 'error'));
}

// --- PASSO 2: BUSCAR E INSERIR TIPOS ---
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

$tipos_map_local = [];
$resultado_tipos = mysqli_query($conexao, "SELECT id, nome FROM `tipos`");
while ($tipo = mysqli_fetch_assoc($resultado_tipos)) {
    $tipos_map_local[$tipo['nome']] = $tipo['id'];
}
print_status(count($tipos_map_local) . " tipos inseridos e mapeados.", 'success');

// --- PASSO 3: BUSCAR E INSERIR POKÉMON ---
print_status("PASSO 3: Buscando e inserindo os 151 Pokémon...", 'title');
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

    usleep(50000); // Pausa para não sobrecarregar a API
}

// --- PASSO 4: INSERÇÃO FINAL DOS POKÉMON ---
print_status("PASSO 4: Inserindo Pokémon na base de dados...", 'title');
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