<?php
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = [];

// Destruir o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial com caminho absoluto
header("Location: /desaparecidos/index.php");
exit();
?>