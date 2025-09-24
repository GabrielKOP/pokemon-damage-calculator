<?php
session_start();
require_once 'config/db.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($conexao === false) {
        $mensagem = "Erro de configuração da base de dados."; 
        $tipo_mensagem = 'error';
    } else {
        $email = $_POST['email'];
        // --- LINHA CORRIGIDA ---
        // Removemos a busca pela coluna 'nome', que não existe.
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt); // Alterado de mysqli_stmt_store_result para get_result

        if ($utilizador = mysqli_fetch_assoc($resultado)) { // Alterado para buscar o resultado
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires = date("Y-m-d H:i:s", time() + 3600); // Token expira em 1 hora

            $sql_update = "UPDATE usuarios SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
            $stmt_update = mysqli_prepare($conexao, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'sss', $token_hash, $expires, $email);
            mysqli_stmt_execute($stmt_update);
            
            $reset_link = "http://localhost/pokemon_calculator/resetar_senha.php?token=" . $token;
            $mensagem = "Se uma conta com este email existir, um link de redefinição foi gerado.<br><br><strong>Link de Teste (Simulação de Email):</strong> <a href='{$reset_link}' target='_blank'>{$reset_link}</a>";
            $tipo_mensagem = 'success';

        } else {
            $mensagem = "Se uma conta com este email existir, um link de redefinição foi gerado.";
            $tipo_mensagem = 'success';
        }
        mysqli_close($conexao);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><title>Recuperar Senha</title><link rel="stylesheet" href="public/css/auth.css">
<style>.login-container a { color: #007bff; text-decoration: none; } .login-container a:hover { text-decoration: underline; } .message { padding: 15px; border-radius: 5px; margin-bottom: 15px; word-break: break-all; } .success { background-color: #d4edda; color: #155724; } .error { background-color: #f8d7da; color: #721c24; }</style>
</head>
<body>
    <div class="auth-container">
        <h2>Recuperar Senha</h2>
        <p style="margin-bottom: 1rem; font-size: 0.9em;">Insira o seu email para receber um link de redefinição de senha.</p>
        <?php if ($mensagem): ?><div class="message <?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div><?php endif; ?>
        
        <form action="esqueci_senha.php" method="POST" <?php if ($tipo_mensagem == 'success') echo 'style="display:none;"'; ?>>
            <div>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <button type="submit" class="auth-button">Enviar Link</button>
        </form>
        <p style="text-align: center; margin-top: 1rem;">
            <a href="login.php">Voltar ao login</a>
        </p>
    </div>
</body>
</html>