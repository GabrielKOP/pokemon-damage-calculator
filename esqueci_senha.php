<?php
session_start();
require_once 'config/db.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($conexao === false) {
        $mensagem = "Erro de configuração da base de dados."; $tipo_mensagem = 'error';
    } else {
        $email = $_POST['email'];
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires = date("Y-m-d H:i:s", time() + 3600); // Token expira em 1 hora

            $sql_update = "UPDATE usuarios SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
            $stmt_update = mysqli_prepare($conexao, $sql_update);
            mysqli_stmt_bind_param($stmt_update, 'sss', $token_hash, $expires, $email);
            mysqli_stmt_execute($stmt_update);
            
            // --- SIMULAÇÃO DE ENVIO DE EMAIL ---
            // Num projeto real, aqui enviarias um email para o utilizador.
            // Para o nosso projeto local, vamos mostrar o link diretamente no ecrã.
            $reset_link = "http://localhost/pokemon_calculator/resetar_senha.php?token=" . $token;
            $mensagem = "Se uma conta com este email existir, um link de redefinição foi gerado.<br><br><strong>Link de Teste (Simulação de Email):</strong> <a href='{$reset_link}'>{$reset_link}</a>";
            $tipo_mensagem = 'success';

        } else {
            $mensagem = "Se uma conta com este email existir, um link de redefinição foi gerado.";
            $tipo_mensagem = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><title>Recuperar Senha</title><link rel="stylesheet" href="public/css/style.css">
<style>.login-container a { color: #007bff; } .message { padding: 15px; border-radius: 5px; margin-bottom: 15px; } .success { background-color: #d4edda; color: #155724; } .error { background-color: #f8d7da; color: #721c24; }</style>
</head>
<body>
    <div class="login-container" style="max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #333; font-family: Arial, sans-serif;">
        <h2>Recuperar Senha</h2>
        <p>Insira o seu email para receber um link de redefinição de senha.</p>
        <?php if ($mensagem): ?><div class="message <?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div><?php endif; ?>
        
        <form action="esqueci_senha.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <button type="submit" id="calcular_btn" style="width: 100%; border: none; font-size: 1em;">Enviar Link</button>
        </form>
        <p style="text-align: center; margin-top: 15px;"><a href="login.php">Voltar ao login</a></p>
    </div>
</body>
</html>