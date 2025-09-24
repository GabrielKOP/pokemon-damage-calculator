<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// --- LINHA DE SEGURANÇA ATUALIZADA ---
// Garante que o utilizador está logado, que o seu perfil é 'admin' e que um pokemon_id foi enviado
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'admin' || !isset($_GET['pokemon_id'])) {
    http_response_code(403); // Forbidden (Acesso Proibido)
    echo json_encode(['error' => 'Acesso negado ou ID do Pokémon em falta.']);
    exit;
}

$pokemon_id = (int)$_GET['pokemon_id'];
$response = [
    'todos_golpes' => [],
    'golpes_conhecidos' => []
];

// Query 1: Busca todos os 165 golpes
$sql_todos_golpes = "SELECT id, nome FROM golpes ORDER BY nome ASC";
$resultado_todos = mysqli_query($conexao, $sql_todos_golpes);
if ($resultado_todos) {
    while ($golpe = mysqli_fetch_assoc($resultado_todos)) {
        $response['todos_golpes'][] = $golpe;
    }
}


// Query 2: Busca os IDs dos golpes que o Pokémon selecionado já conhece
$sql_conhecidos = "SELECT golpe_id FROM pokemon_golpes WHERE pokemon_id = ?";
$stmt = mysqli_prepare($conexao, $sql_conhecidos);
mysqli_stmt_bind_param($stmt, 'i', $pokemon_id);
mysqli_stmt_execute($stmt);
$resultado_conhecidos = mysqli_stmt_get_result($stmt);
if ($resultado_conhecidos) {
    while ($linha = mysqli_fetch_assoc($resultado_conhecidos)) {
        $response['golpes_conhecidos'][] = $linha['golpe_id'];
    }
}
mysqli_stmt_close($stmt);

echo json_encode($response);
mysqli_close($conexao);
?>