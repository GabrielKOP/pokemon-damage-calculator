<?php
// helpers.php

/**
 * Redireciona o utilizador para uma nova URL enviando headers que previnem a cache do navegador.
 *
 * @param string $url A URL para a qual redirecionar.
 * @return void
 */
function redirecionar_com_cache_limpa($url) {
    // Headers para instruir o navegador a não guardar esta resposta em cache.
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    // O redirecionamento em si
    header("Location: " . $url);
    
    // Termina a execução do script para garantir que nada mais é executado.
    exit;
}

?>