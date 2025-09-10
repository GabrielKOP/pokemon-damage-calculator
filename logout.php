<?php
session_start(); // Inicia a sessão para poder aceder a ela

session_unset(); // Remove todas as variáveis da sessão (ex: 'user_id')

session_destroy(); // Destrói a sessão por completo

// Redireciona o utilizador para a página de login
header("Location: login.php");
exit;
?>