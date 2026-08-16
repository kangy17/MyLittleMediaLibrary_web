<?php

session_start();

$_SESSION = [];

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

session_destroy();

?>

<script>

localStorage.removeItem("isLoggedIn");
localStorage.removeItem("username");
localStorage.removeItem("dialogClosed");

window.location.href = "index.html";

</script>