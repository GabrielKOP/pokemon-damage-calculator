<?php
session_start();
require_once '../helpers.php'; // O caminho para o helpers.php muda aqui

if (!isset($_SESSION['user_id']) || $_SESSION['user_perfil'] !== 'admin') {
    redirecionar_com_cache_limpa('../login.php');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 20px;">
        <h1>Bem-vindo ao Painel de Administração!</h1>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['user_email']); ?>!</p>
        <p>Selecione uma ferramenta abaixo para começar a gerir o conteúdo do site.</p>
        <br>
        <nav>
            <ul>
                <li><a href="gerir_golpes.php">Gerir Golpes dos Pokémon</a></li>
            </ul>
        </nav>
        <br>
        <a href="../logout.php">Sair (Logout)</a>
    </div>
</body>
</html>