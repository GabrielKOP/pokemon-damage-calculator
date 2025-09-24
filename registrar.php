<?php
session_start();
require_once 'config/db.php';

if ($conexao === false) {
    die("<h1>Bem-vindo!</h1><p>A base de dados ainda não foi configurada. Por favor, execute os scripts de setup primeiro.</p>");
}

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "O formato do email é inválido.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt_check = mysqli_prepare($conexao, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 's', $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $erro = "Este email já está registado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $perfil = 'comum';

            $sql_insert = "INSERT INTO usuarios (email, senha, perfil) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conexao, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, 'sss', $email, $senha_hash, $perfil);

            if (mysqli_stmt_execute($stmt_insert)) {
                $_SESSION['sucesso_registo'] = "Conta criada com sucesso! Podes agora fazer o login.";
                header("Location: login.php");
                exit;
            } else {
                $erro = "Ocorreu um erro. Tente novamente.";
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registar Nova Conta</title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Criar Nova Conta</h2>
        <?php if ($erro): ?><p class="message error"><?php echo $erro; ?></p><?php endif; ?>
        
        <form action="registrar.php" method="POST">
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div>
                <label for="senha">Senha (mín. 8 caracteres):</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            <div>
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" required>
            </div>
            <button type="submit" class="auth-button">Registar</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            Já tem uma conta? <a href="login.php">Faça o login</a>
        </p>
    </div>
</body>
</html>