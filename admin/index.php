<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
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
                <li><a href="cadastrar_admin.php">Cadastrar Novo Administrador</a></li>
            </ul>
        </nav>
        <br>
        <a href="../logout.php">Sair (Logout)</a>
    </div>
</body>
</html>
