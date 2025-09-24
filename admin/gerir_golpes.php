<?php
session_start();
require_once '../config/db.php';

// --- LINHA DE SEGURANÇA ATUALIZADA ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensagem_sucesso = '';

// NOVO: Lógica para processar o formulário quando enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pokemon_id = (int)$_POST['pokemon_id'];
    // Os golpes chegam como um array de IDs das checkboxes marcadas
    $golpes_selecionados = isset($_POST['golpes']) ? $_POST['golpes'] : [];

    // Para garantir consistência, primeiro apagamos todas as ligações antigas para este Pokémon
    $sql_delete = "DELETE FROM pokemon_golpes WHERE pokemon_id = ?";
    $stmt_delete = mysqli_prepare($conexao, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, 'i', $pokemon_id);
    mysqli_stmt_execute($stmt_delete);
    mysqli_stmt_close($stmt_delete);

    // Agora, inserimos as novas ligações, se houver alguma
    if (!empty($golpes_selecionados)) {
        $sql_insert = "INSERT INTO pokemon_golpes (pokemon_id, golpe_id) VALUES (?, ?)";
        $stmt_insert = mysqli_prepare($conexao, $sql_insert);
        
        foreach ($golpes_selecionados as $golpe_id) {
            $golpe_id_int = (int)$golpe_id;
            mysqli_stmt_bind_param($stmt_insert, 'ii', $pokemon_id, $golpe_id_int);
            mysqli_stmt_execute($stmt_insert);
        }
        mysqli_stmt_close($stmt_insert);
    }
    $mensagem_sucesso = "Golpes do Pokémon atualizados com sucesso!";
}


// Lógica para buscar todos os Pokémon para o menu dropdown
$sql_pokemon = "SELECT id, nome FROM pokemon ORDER BY id ASC";
$resultado_pokemon = mysqli_query($conexao, $sql_pokemon);

// Se o formulário foi enviado, precisamos de buscar novamente os dados do Pokémon selecionado
// para manter o dropdown selecionado na opção correta.
$pokemon_id_selecionado = isset($pokemon_id) ? $pokemon_id : null;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Golpes de Pokémon</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 20px;">
        <a href="index.php">&larr; Voltar ao Painel</a>
        <h1>Gerir Golpes de Pokémon</h1>
        <p>Selecione um Pokémon para ver e editar os golpes que ele pode aprender.</p>
        
        <?php if ($mensagem_sucesso): ?>
            <p style="color: green; font-weight: bold;"><?php echo $mensagem_sucesso; ?></p>
        <?php endif; ?>

        <form action="gerir_golpes.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label for="pokemon_id">Pokémon:</label>
                <select name="pokemon_id" id="pokemon_id">
                    <option value="">-- Selecione um Pokémon --</option>
                    <?php while ($pokemon = mysqli_fetch_assoc($resultado_pokemon)) : ?>
                        <option value="<?php echo $pokemon['id']; ?>" <?php if($pokemon['id'] == $pokemon_id_selecionado) echo 'selected'; ?>>
                            #<?php echo $pokemon['id']; ?> - <?php echo htmlspecialchars($pokemon['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div id="lista_golpes_container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
                Selecione um Pokémon para ver a lista de golpes.
            </div>

            <br>
            <button type="submit" id="calcular_btn">Salvar Alterações</button>
        </form>
    </div>

    <script src="../public/js/admin.js"></script>
</body>
</html>