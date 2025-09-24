<?php
session_start();
require_once 'config/db.php';

$token = $_GET['token'] ?? '';
$erro = '';
$token_valido = false;

if ($conexao === false) {
    $erro = "Erro de configuração da base de dados.";
} elseif (empty($token)) {
    $erro = "Token não fornecido.";
} else {
    $token_hash = hash('sha256', $token);
    $sql = "SELECT id, reset_token_expires_at FROM usuarios WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token_hash);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $utilizador = mysqli_fetch_assoc($resultado);

    if ($utilizador) {
        $token_valido = true;
    } else {
        $erro = "Link de redefinição inválido ou expirado.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if (strlen($senha) < 8) {
        $erro = "A nova senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $nova_senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql_update = "UPDATE usuarios SET senha = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
        $stmt_update = mysqli_prepare($conexao, $sql_update);
        mysqli_stmt_bind_param($stmt_update, 'si', $nova_senha_hash, $utilizador['id']);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['sucesso_reset'] = "Senha redefinida com sucesso! Podes agora fazer o login.";
            header("Location: login.php");
            exit;
        } else {
            $erro = "Ocorreu um erro ao redefinir a senha.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Redefinir Nova Senha</h2>
        <?php if ($erro): ?><p class="message error"><?php echo $erro; ?></p><?php endif; ?>
        
        <?php if ($token_valido): ?>
        <form action="resetar_senha.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <div>
                <label for="senha">Nova Senha:</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            <div>
                <label for="confirmar_senha">Confirmar Nova Senha:</label>
                <input type="password" name="confirmar_senha" id="confirmar_senha" required>
            </div>
            <button type="submit" class="auth-button">Redefinir Senha</button>
        </form>
        <?php else: ?>
        <p style="text-align: center; margin-top: 1rem;"><a href="esqueci_senha.php">Pedir novo link</a></p>
        <?php endif; ?>
    </div>
</body>
</html>