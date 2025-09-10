<?php
// Inicia a sessão no topo de CADA página que precisa dela
session_start();

// Se o utilizador já estiver logado, redireciona para o painel de admin
if (isset($_SESSION['user_id'])) {
    header("Location: admin/index.php");
    exit;
}

require_once 'config/db.php';
$erro_login = '';

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $erro_login = "Por favor, preencha ambos os campos.";
    } else {
        // Busca o utilizador na base de dados
        $sql = "SELECT id, email, senha, perfil FROM usuarios WHERE email = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if ($utilizador = mysqli_fetch_assoc($resultado)) {
            // Utilizador encontrado, agora verifica a senha
            // Compara a senha enviada com o hash guardado no banco de dados
            if (password_verify($senha, $utilizador['senha'])) {
                // Senha correta! Inicia a sessão.
                $_SESSION['user_id'] = $utilizador['id'];
                $_SESSION['user_email'] = $utilizador['email'];
                $_SESSION['user_perfil'] = $utilizador['perfil'];

                // Redireciona para a página de administração
                header("Location: admin/index.php");
                exit; // Garante que o script para após o redirecionamento
            } else {
                $erro_login = "Email ou senha inválidos.";
            }
        } else {
            $erro_login = "Email ou senha inválidos.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conexao);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    <link rel="stylesheet" href="public/css/style.css"> </head>
<body>
    <div class="login-container" style="max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2>Login de Administrador</h2>
        <form action="login.php" method="POST">
            <div style="margin-bottom: 15px;">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div style="margin-bottom: 15px;">
                <label for="senha">Senha:</label>
                <input type="password" name="senha" id="senha" required>
            </div>
            <?php if ($erro_login): ?>
                <p style="color: red;"><?php echo $erro_login; ?></p>
            <?php endif; ?>
            <button type="submit" id="calcular_btn" style="width: 100%; border: none;">Entrar</button>
        </form>
    </div>
</body>
</html>