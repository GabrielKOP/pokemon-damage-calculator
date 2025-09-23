<?php
session_start();
require_once '../config/db.php';

// Segurança: Apenas utilizadores logados podem aceder a esta página
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$erro = '';
$sucesso = '';

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // 1. Validação dos Dados
    if (empty($email) || empty($senha) || empty($confirmar_senha)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "O formato do email é inválido.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // 2. Verifica se o email já existe
        $sql_check = "SELECT id FROM usuarios WHERE email = ?";
        $stmt_check = mysqli_prepare($conexao, $sql_check);
        mysqli_stmt_bind_param($stmt_check, 's', $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $erro = "Este email já está registado.";
        } else {
            // 3. Se tudo estiver OK, insere o novo administrador
            
            // Encripta a senha
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $perfil = 'admin';

            $sql_insert = "INSERT INTO usuarios (email, senha, perfil) VALUES (?, ?, ?)";
            $stmt_insert = mysqli_prepare($conexao, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, 'sss', $email, $senha_hash, $perfil);

            if (mysqli_stmt_execute($stmt_insert)) {
                $sucesso = "Novo administrador '{$email}' registado com sucesso!";
            } else {
                $erro = "Ocorreu um erro ao registar o novo administrador. Tente novamente.";
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
    <title>Cadastrar Administrador</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div style="max-width: 500px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #333;">
        <a href="index.php">&larr; Voltar ao Painel</a>
        <h2 style="text-align: center; margin-bottom: 20px;">Cadastrar Novo Administrador</h2>

        <?php if ($erro): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px;"><?php echo $erro; ?></p>
        <?php endif; ?>
        <?php if ($sucesso): ?>
            <p style="color: green; text-align: center; margin-bottom: 15px;"><?php echo $sucesso; ?></p>
        <?php endif; ?>

        <form action="cadastrar_admin.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="email">Email do Novo Admin:</label>
                <input type="email" name="email" id="email" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <div style="margin-bottom: 20px;">
                <label for="confirmar_senha">Confirmar Senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <button type="submit" id="calcular_btn" style="width: 100%; border: none;">Registar</button>
        </form>
    </div>
</body>
</html>
