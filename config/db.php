<?php
// config/db.php

// --- Credenciais do Banco de Dados ---
// Estas são as credenciais padrão do XAMPP.
$servidor = "localhost";    // O servidor onde o MySQL está a correr (geralmente localhost)
$usuario = "root";          // O utilizador padrão do MySQL no XAMPP
$senha = "";                // A senha padrão do MySQL no XAMPP é vazia
$banco = "pokemon_calculator"; // O nome do banco de dados que criámos

// --- Criar a Conexão ---
// Usamos a função mysqli_connect para tentar ligar ao banco de dados.
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// --- Verificar a Conexão ---
// É uma boa prática verificar sempre se a conexão foi bem-sucedida.
// Se falhar, o script para e exibe uma mensagem de erro.
if (!$conexao) {
    die("Falha na conexão: " . mysqli_connect_error());
}

// Opcional: Definir o charset para UTF-8 para evitar problemas com acentos
mysqli_set_charset($conexao, "utf8");
?>