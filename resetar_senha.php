<?php
session_start();
require_once 'helpers.php'; // Inclui as nossas funções de ajuda (para o redirecionamento)
require_once 'config/db.php'; // Inclui a conexão com a base de dados

$token = $_GET['token'] ?? '';
$erro = '';
$token_valido = false;

// Primeiro, verificamos se a conexão com a BD foi bem sucedida
if ($conexao === false) {
    $erro = "Erro de configuração da base de dados. Por favor, tente mais tarde.";
} elseif (empty($token)) {
    // Verifica se um token foi fornecido na URL
    $erro = "Token não fornecido. O link pode estar incompleto.";
} else {
    // Se um token foi fornecido, vamos validá-lo
    $token_hash = hash('sha256', $token);
    
    // Procura um utilizador com este hash de token E que o token não tenha expirado
    $sql = "SELECT id, reset_token_expires_at FROM usuarios WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token_hash);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $utilizador = mysqli_fetch_assoc($resultado);

    if ($utilizador) {
        // Se encontrámos um utilizador, o token é válido
        $token_valido = true;
    } else {
        $erro = "Link de redefinição inválido ou expirado. Por favor, peça um novo.";
    }
}

// Verifica se o formulário de NOVA senha foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valido) {
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Valida a nova senha
    if (strlen($senha) < 8) {
        $erro = "A nova senha deve ter no mínimo 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Se a nova senha for válida, atualiza-a na base de dados
        $nova_senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Atualiza a senha e LIMPA os campos do token para que não possa ser reutilizado
        $sql_update = "UPDATE usuarios SET senha = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
        $stmt_update = mysqli_prepare($conexao, $sql_update);
        mysqli_stmt_bind_param($stmt_update, 'si', $nova_senha_hash, $utilizador['id']);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $_SESSION['sucesso_reset'] = "Senha redefinida com sucesso! Podes agora fazer o login.";
            redirecionar_com_cache_limpa('login.php');
        } else {
            $erro = "Ocorreu um erro ao redefinir a senha. Tente novamente.";
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
    <link rel="stylesheet" href="public/css/auth.css?v=1.0">
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