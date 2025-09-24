<?php
session_start();
require_once 'helpers.php'; // Inclui o nosso novo ficheiro de ajudantes

session_unset();
session_destroy();

redirecionar_com_cache_limpa('login.php');
?>