<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// Segurança: verifica se o utilizador está logado E se um pokemon_id foi enviado
if (!isset($_SESSION['user_id']) || !isset($_GET['pokemon_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Acesso negado ou ID do Pokémon em falta.']);
    exit;
}

$pokemon_id = (int)$_GET['pokemon_id'];
$response = [
    'todos_golpes' => [],
    'golpes_conhecidos' => []
];

// Query 1: Busca todos os 165 golpes (VERSÃO CORRIGIDA E SEGURA)
$sql_todos_golpes = "SELECT id, nome FROM golpes ORDER BY nome ASC";
$resultado_todos = mysqli_query($conexao, $sql_todos_golpes);
// Verificação para garantir que a query funcionou
if ($resultado_todos) {
    while ($golpe = mysqli_fetch_assoc($resultado_todos)) {
        $response['todos_golpes'][] = $golpe;
    }
}

// Query 2: Busca os IDs dos golpes que o Pokémon selecionado já conhece (esta já estava correta)
$sql_conhecidos = "SELECT golpe_id FROM pokemon_golpes WHERE pokemon_id = ?";
$stmt = mysqli_prepare($conexao, $sql_conhecidos);
mysqli_stmt_bind_param($stmt, 'i', $pokemon_id);
mysqli_stmt_execute($stmt);
$resultado_conhecidos = mysqli_stmt_get_result($stmt);
if ($resultado_conhecidos) {
    while ($linha = mysqli_fetch_assoc($resultado_conhecidos)) {
        // Adiciona apenas o número do ID ao array para ser mais leve
        $response['golpes_conhecidos'][] = $linha['golpe_id'];
    }
}
mysqli_stmt_close($stmt);

// Envia a resposta final em formato JSON
echo json_encode($response);
mysqli_close($conexao);

?>