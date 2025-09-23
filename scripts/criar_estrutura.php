<?php
// scripts/criar_estrutura.php (VERSÃO COM FEEDBACK DE PROGRESSO)

set_time_limit(120); // 2 minutos, mais que suficiente

// Adicionamos a nossa função padrão para dar feedback
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

// Imprime um cabeçalho se estiver a ser executado no navegador
if (php_sapi_name() !== 'cli') {
    echo "<h1>Iniciando Criação da Estrutura do Banco de Dados</h1>";
}

// --- PASSO 1: CONEXÃO BÁSICA E CRIAÇÃO DA BASE DE DADOS ---
print_status("PASSO 1: Conectando ao MySQL...", 'title');

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pokemon_calculator";

$conexao = mysqli_connect($servidor, $usuario, $senha);
if (!$conexao) {
    die(print_status("Erro crítico: Não foi possível conectar ao servidor MySQL. " . mysqli_connect_error(), 'error'));
}
print_status("Conexão com o servidor MySQL estabelecida com sucesso.", 'success');

$sql_create_db = "CREATE DATABASE IF NOT EXISTS " . $banco;
if (mysqli_query($conexao, $sql_create_db)) {
    print_status("Base de dados '{$banco}' verificada/criada com sucesso.", 'success');
} else {
    die(print_status("Erro ao criar a base de dados: " . mysqli_error($conexao), 'error'));
}

// Seleciona a base de dados para os comandos seguintes
mysqli_select_db($conexao, $banco);
mysqli_set_charset($conexao, "utf8");

// --- PASSO 2: (RE)CRIAÇÃO DAS TABELAS ---
print_status("PASSO 2: Destruindo tabelas antigas e criando a nova estrutura...", 'title');

$sql_schema = "
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `pokemon_golpes`, `tipo_eficacia`, `usuarios`, `pokemon`, `golpes`, `tipos`;

CREATE TABLE `tipos` ( `id` INT PRIMARY KEY AUTO_INCREMENT, `nome` VARCHAR(50) NOT NULL UNIQUE );
CREATE TABLE `pokemon` ( `id` INT PRIMARY KEY, `nome` VARCHAR(100) NOT NULL, `tipo1_id` INT NOT NULL, `tipo2_id` INT NULL, `hp` INT NOT NULL, `ataque` INT NOT NULL, `defesa` INT NOT NULL, `especial` INT NOT NULL, `velocidade` INT NOT NULL, FOREIGN KEY (`tipo1_id`) REFERENCES `tipos`(`id`), FOREIGN KEY (`tipo2_id`) REFERENCES `tipos`(`id`) );
CREATE TABLE `golpes` ( `id` INT PRIMARY KEY AUTO_INCREMENT, `nome` VARCHAR(100) NOT NULL, `poder` INT NOT NULL, `tipo_id` INT NOT NULL, `categoria` ENUM('fisico', 'especial') NOT NULL, FOREIGN KEY (`tipo_id`) REFERENCES `tipos`(`id`) );
CREATE TABLE `usuarios` ( `id` INT PRIMARY KEY AUTO_INCREMENT, `email` VARCHAR(255) NOT NULL UNIQUE, `senha` VARCHAR(255) NOT NULL, `perfil` ENUM('admin', 'comum') NOT NULL DEFAULT 'comum' );
CREATE TABLE `pokemon_golpes` ( `pokemon_id` INT NOT NULL, `golpe_id` INT NOT NULL, PRIMARY KEY (`pokemon_id`, `golpe_id`), FOREIGN KEY (`pokemon_id`) REFERENCES `pokemon`(`id`), FOREIGN KEY (`golpe_id`) REFERENCES `golpes`(`id`) );
CREATE TABLE `tipo_eficacia` ( `atacante_tipo_id` INT NOT NULL, `defensor_tipo_id` INT NOT NULL, `multiplicador` DECIMAL(2,1) NOT NULL, PRIMARY KEY (`atacante_tipo_id`, `defensor_tipo_id`), FOREIGN KEY (`atacante_tipo_id`) REFERENCES `tipos`(`id`), FOREIGN KEY (`defensor_tipo_id`) REFERENCES `tipos`(`id`) );

SET FOREIGN_KEY_CHECKS = 1;
";

if (mysqli_multi_query($conexao, $sql_schema)) {
    while (mysqli_next_result($conexao)) {;}
    print_status("Estrutura de tabelas criada com sucesso.", 'success');
} else {
    die(print_status("Erro ao criar a estrutura das tabelas: " . mysqli_error($conexao), 'error'));
}

print_status("<h2>Processo Concluído!</h2>", 'title');
mysqli_close($conexao);
?>