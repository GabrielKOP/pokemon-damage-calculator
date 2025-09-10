<?php
// ---- CÓDIGO DE DEPURAÇÃO: Adiciona estas 3 linhas ----
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------------------------
session_start();

// Verifica se a variável de sessão 'user_id' NÃO existe.
// Se não existir, significa que o utilizador não está logado.
if (!isset($_SESSION['user_id'])) {
    // Redireciona o utilizador para a página de login
    header("Location: ../login.php");
    exit;
}

// Se o script continuar, significa que o utilizador está logado.
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="../public/css/style.css"> </head>
<body>
    <div style="max-width: 800px; margin: 50px auto; padding: 20px;">
        <h1>Bem-vindo ao Painel de Administração!</h1>
        <p>Olá, <?php echo htmlspecialchars($_SESSION['user_email']); ?>!</p>
        <p>A partir daqui, poderemos criar as ferramentas para gerir os Pokémon e os seus golpes.</p>
        <br>
        <a href="../logout.php">Sair (Logout)</a>
    </div>
</body>
</html>