<?php
// get_pokemon_details.php
require_once 'config/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do Pokémon não fornecido.']);
    exit;
}

$pokemonId = (int)$_GET['id'];
$pokemonData = []; // Array para guardar todos os dados a serem retornados

// Query 1: Obter os detalhes do Pokémon
$sql_pokemon = "SELECT p.*, t1.nome AS tipo1_nome, t2.nome AS tipo2_nome 
                FROM pokemon p
                JOIN tipos t1 ON p.tipo1_id = t1.id
                LEFT JOIN tipos t2 ON p.tipo2_id = t2.id
                WHERE p.id = ?";
$stmt_pokemon = mysqli_prepare($conexao, $sql_pokemon);
mysqli_stmt_bind_param($stmt_pokemon, 'i', $pokemonId);
mysqli_stmt_execute($stmt_pokemon);
$resultado_pokemon = mysqli_stmt_get_result($stmt_pokemon);

if ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) {
    $pokemonData = $pokemon; // Guarda os detalhes do pokémon
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Pokémon não encontrado.']);
    exit;
}
mysqli_stmt_close($stmt_pokemon);

// Query 2: Obter os golpes do Pokémon
$sql_golpes = "SELECT g.id, g.nome, g.poder, g.categoria, t.nome as tipo_nome 
               FROM golpes g
               JOIN pokemon_golpes pg ON g.id = pg.golpe_id
               JOIN tipos t ON g.tipo_id = t.id
               WHERE pg.pokemon_id = ?";
$stmt_golpes = mysqli_prepare($conexao, $sql_golpes);
mysqli_stmt_bind_param($stmt_golpes, 'i', $pokemonId);
mysqli_stmt_execute($stmt_golpes);
$resultado_golpes = mysqli_stmt_get_result($stmt_golpes);

$golpes = [];
while ($golpe = mysqli_fetch_assoc($resultado_golpes)) {
    $golpes[] = $golpe;
}
$pokemonData['golpes'] = $golpes; // Adiciona o array de golpes aos dados do Pokémon
mysqli_stmt_close($stmt_golpes);

// Retorna tudo como um único JSON
header('Content-Type: application/json');
echo json_encode($pokemonData);

mysqli_close($conexao);
?>