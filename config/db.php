<?php
// config/db.php (VERSÃO DEFINITIVA E SEGURA PARA PHP 8+)

// --- Credenciais do Banco de Dados ---
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "pokemon_calculator";

// --- Criar a Conexão (de forma segura contra exceções) ---

// Guarda o modo de relatório de erros atual do MySQLi para o podermos restaurar depois
$driver = new mysqli_driver();
$report_mode_original = $driver->report_mode;

// Desativa temporariamente as exceções. Agora, em caso de erro, as funções devolverão `false`
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Tenta conectar-se ao SERVIDOR MySQL. A @ suprime os avisos padrão.
$conexao = @mysqli_connect($servidor, $usuario, $senha);

// 2. Só se a conexão ao SERVIDOR funcionar, tentamos selecionar a BASE DE DADOS.
if ($conexao) {
    // Esta função agora devolverá `false` em caso de erro, em vez de uma exceção.
    if (!mysqli_select_db($conexao, $banco)) {
        // Se a base de dados não for encontrada, definimos a conexão como 'false'.
        $conexao = false;
    } else {
        // Se a base de dados foi encontrada, definimos o charset.
        mysqli_set_charset($conexao, "utf8");
    }
}

// 3. AGORA, depois de todas as operações de risco, restauramos o modo de relatório de erros.
mysqli_report($report_mode_original);

// Este ficheiro agora devolverá:
// - Um objeto de conexão se tudo estiver OK.
// - `false` se a base de dados não existir.
// - `null` ou `false` se a conexão ao próprio MySQL falhar.
?>