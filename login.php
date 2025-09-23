<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit;
}

require_once 'config/db.php';
$erro_login = '';
$sucesso = '';

if (isset($_SESSION['sucesso_registo'])) {
    $sucesso = $_SESSION['sucesso_registo'];
    unset($_SESSION['sucesso_registo']);
}
if (isset($_SESSION['sucesso_reset'])) {
    $sucesso = $_SESSION['sucesso_reset'];
    unset($_SESSION['sucesso_reset']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($conexao === false) {
        $erro_login = "Erro de configuração: A base de dados não foi encontrada.";
    } else {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        if (empty($email) || empty($senha)) {
            $erro_login = "Por favor, preencha ambos os campos.";
        } else {
            $sql = "SELECT id, email, senha, perfil FROM usuarios WHERE email = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            if ($utilizador = mysqli_fetch_assoc($resultado)) {
                if (password_verify($senha, $utilizador['senha'])) {
                    $_SESSION['user_id'] = $utilizador['id'];
                    $_SESSION['user_email'] = $utilizador['email'];
                    $_SESSION['user_perfil'] = $utilizador['perfil'];
                    header("Location: admin/index.php");
                    exit;
                } else {
                    $erro_login = "Email ou senha inválidos.";
                }
            } else {
                $erro_login = "Email ou senha inválidos.";
            }
            mysqli_stmt_close($stmt);
        }
        if ($conexao) mysqli_close($conexao);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="public/css/style.css">
    <style>.login-container a { color: #007bff; text-decoration: none; } .login-container a:hover { text-decoration: underline; }</style>
</head>
<body>
    <div class="login-container" style="max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); color: #333; font-family: Arial, sans-serif;">
        <h2 style="text-align:center;">Login</h2>
        <?php if (!empty($erro_login)): ?><p style="color: red; text-align:center;"><?php echo $erro_login; ?></p><?php endif; ?>
        <?php if (!empty($sucesso)): ?><p style="color: green; text-align:center;"><?php echo $sucesso; ?></p><?php endif; ?>
        
        <form action="login.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <div style="margin-bottom: 15px;">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required style="width: 100%; padding: 8px; font-family: Arial;">
            </div>
            <button type="submit" id="calcular_btn" style="width: 100%; border: none; font-size: 1em;">Entrar</button>
        </form>

        <div style="display: flex; justify-content: space-between; margin-top: 15px; font-size: 0.8em;">
            <a href="esqueci_senha.php">Esqueceu a senha?</a>
            <a href="registrar.php">Não tem uma conta? Registe-se</a>
        </div>
    </div>
</body>
</html>