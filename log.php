<?php
session_start();
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "kont_db";

$conn = new mysqli(
    $servername,
    $db_username,
    $db_password,
    $dbname
);

if ($conn->connect_error) {
    die("Ошибка подключения к базе данных.");
}

$conn->set_charset("utf8mb4");


/*
|--------------------------------------------------------------------------
| ТЕКУЩИЙ РЕЖИМ
|--------------------------------------------------------------------------
*/

$mode = isset($_GET['mode']) && $_GET['mode'] === 'register'
    ? 'register'
    : 'login';


$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| ОБРАБОТКА ФОРМ
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | РЕГИСТРАЦИЯ
    |--------------------------------------------------------------------------
    */

    if ($action === "register") {

        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";


        if ($username === "" || $password === "") {

            $message = "Заполните все поля.";
            $messageType = "error";

            $mode = "register";

        } elseif (mb_strlen($username) < 3) {

            $message = "Имя пользователя должно содержать минимум 3 символа.";
            $messageType = "error";

            $mode = "register";

        } elseif (mb_strlen($password) < 6) {

            $message = "Пароль должен содержать минимум 6 символов.";
            $messageType = "error";

            $mode = "register";

        } else {

            /*
             * Проверяем существование пользователя
             */

            $stmt = $conn->prepare(
                "SELECT id FROM users WHERE username = ? LIMIT 1"
            );

            $stmt->bind_param("s", $username);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows > 0) {

                $message =
                    "Пользователь с таким именем уже существует.";

                $messageType = "error";

                $mode = "register";

            } else {

                /*
                 * Хешируем пароль
                 */

                $hashedPassword =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                /*
                 * Добавляем пользователя
                 */

                $stmt = $conn->prepare(
                    "INSERT INTO users (username, password)
                     VALUES (?, ?)"
                );

                $stmt->bind_param(
                    "ss",
                    $username,
                    $hashedPassword
                );


                if ($stmt->execute()) {

                    /*
                     * После регистрации
                     * переходим обратно на вход
                     */

                    header(
                        "Location: log.php?registered=1"
                    );

                    exit;

                } else {

                    $message =
                        "Ошибка регистрации. Попробуйте снова.";

                    $messageType = "error";

                    $mode = "register";
                }
            }

            $stmt->close();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ВХОД
    |--------------------------------------------------------------------------
    */

    if ($action === "login") {

        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $age = (int)($_POST["age"] ?? 0);


        /*
         * Проверяем возраст
         */

        if ($age < 13) {

            $message =
                "Использование сайта разрешено пользователям от 13 лет.";

            $messageType = "error";

            $mode = "login";

        } elseif ($username === "" || $password === "") {

            $message =
                "Заполните все поля.";

            $messageType = "error";

            $mode = "login";

        } else {

            /*
             * Ищем пользователя
             */

           $stmt = $conn->prepare(
    "SELECT id, username, password
     FROM users
     WHERE username = ?
     LIMIT 1"
);

            $stmt->bind_param(
                "s",
                $username
            );

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 0) {

                $message =
                    "Пользователь не найден.";

                $messageType = "error";

                $mode = "login";

            } else {

                $user = $result->fetch_assoc();


               /*
 * Проверяем пароль
 */

if (
    password_verify(
        $password,
        $user["password"]
    )
) {

    /*
     * Создаём PHP-сессию.
     * Теперь сервер знает, какой пользователь вошёл.
     */

    $_SESSION["user_id"] = $user["id"];
    $_SESSION["username"] = $user["username"];


    /*
     * Сохраняем информацию в localStorage
     * для интерфейса сайта.
     */

    echo "
    <script>

        localStorage.setItem(
            'isLoggedIn',
            'true'
        );

        localStorage.setItem(
            'username',
            " . json_encode(
                $user["username"],
                JSON_UNESCAPED_UNICODE
            ) . "
        );

        localStorage.removeItem(
            'dialogClosed'
        );

        window.location.href =
            'index.html';

    </script>
    ";

    exit;

} else {

                    $message =
                        "Неверный пароль.";

                    $messageType = "error";

                    $mode = "login";
                }
            }

            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| СООБЩЕНИЕ ПОСЛЕ РЕГИСТРАЦИИ
|--------------------------------------------------------------------------
*/

if (
    isset($_GET["registered"]) &&
    $_GET["registered"] === "1"
) {

    $message =
        "Регистрация успешна. Теперь войдите в аккаунт.";

    $messageType = "success";

    $mode = "login";
}

?>


<!DOCTYPE html>

<html lang="ru">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Моя медиатека /
        <?php echo $mode === "login"
            ? "Вход"
            : "Регистрация"; ?>
    </title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        html {
            scroll-behavior: smooth;
        }


        body {

            font-family:
                "Segoe UI",
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background: #09090b;

            color: #f2f2f2;

            min-height: 100vh;

            overflow-x: hidden;
        }


        /* =========================
           GRID BACKGROUND
        ========================= */

        body::before {

            content: "";

            position: fixed;

            inset: 0;

            pointer-events: none;

            z-index: -1;

            opacity: 0.08;

            background-image:

                linear-gradient(
                    rgba(255, 64, 129, 0.08) 1px,
                    transparent 1px
                ),

                linear-gradient(
                    90deg,
                    rgba(255, 64, 129, 0.08) 1px,
                    transparent 1px
                );

            background-size: 70px 70px;
        }


        /* =========================
           HEADER
        ========================= */

        header {

            position: fixed;

            top: 0;
            left: 0;
            right: 0;

            height: 76px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 45px;

            background:
                rgba(10, 10, 12, 0.88);

            backdrop-filter:
                blur(15px);

            border-bottom:
                1px solid
                rgba(255, 64, 129, 0.15);

            z-index: 900;

            animation:
                headerAppear
                0.7s ease;
        }


        @keyframes headerAppear {

            from {
                opacity: 0;
                transform:
                    translateY(-30px);
            }

            to {
                opacity: 1;
                transform:
                    translateY(0);
            }
        }


        .header-left {

            display: flex;

            align-items: center;

            gap: 18px;
        }


        .menu-button {

            width: 44px;
            height: 44px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            align-items: center;

            gap: 5px;

            border:
                1px solid
                rgba(255, 64, 129, 0.4);

            background:
                rgba(255, 64, 129, 0.05);

            cursor: pointer;

            transition: 0.3s;
        }


        .menu-button span {

            width: 20px;
            height: 2px;

            background: #ff4081;

            transition: 0.3s;
        }


        .menu-button:hover {

            background: #ff4081;

            box-shadow:
                0 0 25px
                rgba(255, 64, 129, 0.4);
        }


        .menu-button:hover span {
            background: #09090b;
        }


        .developer {

            font-size: 15px;

            color: #b5b5b5;

            letter-spacing: 1px;
        }


        .developer strong {
            color: #ff4081;
        }


        .top-navigation {

            display: flex;

            align-items: center;

            gap: 45px;
        }


        .top-navigation a {

            position: relative;

            color: #dedede;

            text-decoration: none;

            font-size: 16px;

            transition: 0.3s;
        }


        .top-navigation a::after {

            content: "";

            position: absolute;

            left: 0;

            bottom: -8px;

            width: 0;

            height: 2px;

            background: #ff4081;

            transition: 0.3s;
        }


        .top-navigation a:hover {

            color: #ff4081;
        }


        .top-navigation a:hover::after {

            width: 100%;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;

            width: 300px;

            height: 100vh;

            background:
                linear-gradient(
                    180deg,
                    #111116 0%,
                    #09090b 100%
                );

            border-right:
                1px solid
                rgba(255, 64, 129, 0.25);

            z-index: 1100;

            transform:
                translateX(-100%);

            transition:
                transform
                0.45s
                cubic-bezier(.77,0,.18,1);

            padding: 35px 25px;
        }


        .sidebar.active {
            transform: translateX(0);
        }


        .sidebar-logo {

            margin-bottom: 45px;

            padding-bottom: 25px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.08);
        }


        .sidebar-logo .symbol {

            color: #ff4081;

            font-size: 25px;

            font-weight: bold;
        }


        .sidebar-logo h2 {

            font-size: 20px;

            margin-top: 8px;
        }


        .sidebar-logo span {

            color: #ff4081;

            font-size: 12px;

            letter-spacing: 3px;
        }


        .sidebar-close {

            position: absolute;

            top: 25px;
            right: 25px;

            border: none;

            background: transparent;

            color: #888;

            font-size: 25px;

            cursor: pointer;

            transition: 0.3s;
        }


        .sidebar-close:hover {

            color: #ff4081;

            transform:
                rotate(90deg);
        }


        .sidebar-links {

            display: flex;

            flex-direction: column;

            gap: 8px;
        }


        .sidebar-links a {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 16px;

            color: #cfcfcf;

            text-decoration: none;

            border-left:
                2px solid transparent;

            transition: 0.3s;
        }


        .sidebar-links a:hover {

            color: #ff4081;

            background:
                rgba(255,64,129,0.07);

            border-left-color:
                #ff4081;

            padding-left: 22px;
        }


        .sidebar-icon {

            width: 25px;

            text-align: center;

            color: #ff4081;

            font-size: 17px;
        }


        .beta {

            margin-left: auto;

            font-size: 9px;

            padding: 3px 6px;

            border:
                1px solid #ff4081;

            color: #ff4081;
        }


        .sidebar-overlay {

            position: fixed;

            inset: 0;

            background:
                rgba(0,0,0,0.65);

            backdrop-filter:
                blur(4px);

            z-index: 1050;

            opacity: 0;

            visibility: hidden;

            transition: 0.3s;
        }


        .sidebar-overlay.active {

            opacity: 1;

            visibility: visible;
        }


        /* =========================
           AUTH PAGE
        ========================= */

        .auth-page {

            min-height: 100vh;

            padding:
                125px 25px 80px;

            display: flex;

            justify-content: center;

            align-items: center;
        }


        .auth-wrapper {

            width: 100%;

            max-width: 1050px;

            display: grid;

            grid-template-columns:
                1fr 430px;

            gap: 80px;

            align-items: center;

            animation:
                fadeUp
                0.8s ease;
        }


        @keyframes fadeUp {

            from {

                opacity: 0;

                transform:
                    translateY(35px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }


        /* =========================
           LEFT SIDE
        ========================= */

        .auth-intro {

            position: relative;
        }


        .eyebrow {

            color: #ff4081;

            font-family: monospace;

            font-size: 13px;

            letter-spacing: 3px;

            margin-bottom: 25px;
        }


        .auth-intro h1 {

            font-size:
                clamp(55px, 7vw, 100px);

            line-height: 0.9;

            letter-spacing: -5px;

            text-transform: uppercase;

            margin-bottom: 35px;
        }


        .auth-intro h1 span {

            color: #ff4081;

            text-shadow:
                0 0 30px
                rgba(255,64,129,0.3);
        }


        .auth-description {

            color: #888;

            font-size: 17px;

            line-height: 1.7;

            max-width: 550px;
        }


        .auth-decoration {

            margin-top: 45px;

            display: flex;

            gap: 10px;
        }


        .auth-decoration span {

            width: 45px;

            height: 4px;

            background: #333;
        }


        .auth-decoration span:first-child {

            width: 90px;

            background: #ff4081;
        }


        /* =========================
           AUTH CARD
        ========================= */

        .auth-card {

            padding: 38px;

            background:
                rgba(20,20,23,0.92);

            border:
                1px solid #29292e;

            box-shadow:
                0 30px 80px
                rgba(0,0,0,0.45);

            position: relative;

            overflow: hidden;
        }


        .auth-card::before {

            content: "";

            position: absolute;

            top: 0;
            left: 0;

            width: 100%;

            height: 3px;

            background:
                linear-gradient(
                    90deg,
                    #ff4081,
                    #e040fb
                );
        }


        /* =========================
           TABS
        ========================= */

        .auth-tabs {

            display: grid;

            grid-template-columns: 1fr 1fr;

            margin-bottom: 35px;

            border-bottom:
                1px solid #333;
        }


        .auth-tab {

            position: relative;

            padding:
                14px 5px;

            text-align: center;

            color: #777;

            text-decoration: none;

            font-size: 15px;

            transition: 0.3s;
        }


        .auth-tab:hover {

            color: #ddd;
        }


        .auth-tab.active {

            color: #ff4081;
        }


        .auth-tab.active::after {

            content: "";

            position: absolute;

            bottom: -1px;

            left: 0;

            width: 100%;

            height: 2px;

            background: #ff4081;
        }


        .auth-card h2 {

            font-size: 30px;

            margin-bottom: 8px;
        }


        .auth-subtitle {

            color: #666;

            font-size: 13px;

            margin-bottom: 28px;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 13px 15px;

            margin-bottom: 20px;

            font-size: 13px;

            border-left:
                3px solid;
        }


        .message.error {

            color: #ff6b9d;

            background:
                rgba(255,64,129,0.06);

            border-color:
                #ff4081;
        }


        .message.success {

            color: #78e08f;

            background:
                rgba(120,224,143,0.06);

            border-color:
                #78e08f;
        }


        /* =========================
           INPUTS
        ========================= */

        .input-group {

            position: relative;

            margin-bottom: 17px;
        }


        .input-group input {

            width: 100%;

            padding:
                15px 16px;

            background: #111114;

            color: #fff;

            border:
                1px solid #303038;

            outline: none;

            font-size: 15px;

            transition: 0.3s;
        }


        .input-group input::placeholder {

            color: #666;
        }


        .input-group input:focus {

            border-color:
                #ff4081;

            box-shadow:
                0 0 0 3px
                rgba(255,64,129,0.08);
        }


        /* =========================
           BUTTON
        ========================= */

        .auth-button {

            width: 100%;

            padding: 15px;

            border: none;

            background: #ff4081;

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            margin-top: 5px;

            transition: 0.3s;
        }


        .auth-button:hover {

            background: #e040fb;

            box-shadow:
                0 10px 30px
                rgba(255,64,129,0.2);

            transform:
                translateY(-2px);
        }


        .auth-button:active {

            transform:
                translateY(0);
        }


        /* =========================
           BOTTOM TEXT
        ========================= */

        .auth-bottom {

            margin-top: 25px;

            text-align: center;

            color: #666;

            font-size: 13px;
        }


        .auth-bottom a {

            color: #ff4081;

            text-decoration: none;
        }


        .auth-bottom a:hover {

            text-decoration: underline;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            border-top:
                1px solid
                rgba(255,255,255,0.07);

            padding:
                30px 8%;

            display: flex;

            justify-content: space-between;

            color: #666;

            font-size: 13px;
        }


        footer span {
            color: #ff4081;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 850px) {

            .auth-wrapper {

                grid-template-columns: 1fr;

                max-width: 500px;

                gap: 45px;
            }


            .auth-intro {

                text-align: center;
            }


            .auth-description {

                margin: auto;
            }


            .auth-decoration {

                justify-content: center;
            }

        }


        @media (max-width: 650px) {

            header {

                padding: 0 20px;
            }


            .developer {

                display: none;
            }


            .top-navigation {

                gap: 15px;
            }


            .top-navigation a {

                font-size: 13px;
            }


            .auth-page {

                padding:
                    110px 20px 60px;
            }


            .auth-intro h1 {

                font-size: 55px;

                letter-spacing: -3px;
            }


            .auth-card {

                padding: 25px;
            }


            footer {

                flex-direction: column;

                gap: 10px;

                padding:
                    25px 20px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>


<aside
    class="sidebar"
    id="sidebar">


    <button
        class="sidebar-close"
        id="sidebarClose">
        ×
    </button>


    <div class="sidebar-logo">

        <div class="symbol">
            &lt;KRIS /&gt;
        </div>

        <h2>
            My Little Media
        </h2>

        <span>
            MEDIA LIBRARY
        </span>

    </div>


    <nav class="sidebar-links">


        <a href="index.html">

            <span class="sidebar-icon">
                ⌂
            </span>

            Главная

        </a>


        <a href="info.html">

            <span class="sidebar-icon">
                ⓘ
            </span>

            О сайте

        </a>


        <a href="kont.php">

            <span class="sidebar-icon">
                ✉
            </span>

            Контакты

        </a>


        <a href="bands.php">

            <span class="sidebar-icon">
                ♫
            </span>

            Группы

            <span class="beta">
                БЕТА
            </span>

        </a>


        <a href="favorites.php">

            <span class="sidebar-icon">
                ♡
            </span>

            Избранное

        </a>


    </nav>

</aside>


<!-- =========================
     HEADER
========================= -->

<header>


    <div class="header-left">


        <button
            class="menu-button"
            id="menuButton"
            aria-label="Открыть меню">

            <span></span>
            <span></span>
            <span></span>

        </button>


        <div class="developer">

            Developed by
            <strong>Kris</strong>

        </div>


    </div>


    <nav class="top-navigation">

        <a href="info.html">
            О сайте
        </a>

        <a href="index.html">
            Главная
        </a>

        <a href="kont.php">
            Контакты
        </a>

    </nav>


</header>


<!-- =========================
     AUTH
========================= -->

<main class="auth-page">


    <div class="auth-wrapper">


        <!-- LEFT -->

        <section class="auth-intro">


            <div class="eyebrow">
                // MY LITTLE MEDIA LIBRARY
            </div>


            <h1>

                ТВОЯ МУЗЫКА.

                <br>

                <span>
                    ТВОИ ПРАВИЛА.
                </span>

            </h1>


            <p class="auth-description">

                Создайте аккаунт или войдите в свою
                медиатеку, чтобы получить доступ
                к музыкальному контенту и
                персональным возможностям сайта.

            </p>


            <div class="auth-decoration">

                <span></span>
                <span></span>
                <span></span>

            </div>


        </section>


        <!-- RIGHT -->

        <section class="auth-card">


            <!-- TABS -->

            <div class="auth-tabs">


                <a
                    href="log.php"
                    class="auth-tab
                    <?php
                    echo $mode === "login"
                        ? "active"
                        : "";
                    ?>"
                >

                    Вход

                </a>


                <a
                    href="log.php?mode=register"
                    class="auth-tab
                    <?php
                    echo $mode === "register"
                        ? "active"
                        : "";
                    ?>"
                >

                    Регистрация

                </a>


            </div>


            <?php if ($mode === "login"): ?>


                <h2>
                    Вход
                </h2>


                <p class="auth-subtitle">
                    Войдите в свою медиатеку
                </p>


            <?php else: ?>


                <h2>
                    Регистрация
                </h2>


                <p class="auth-subtitle">
                    Создайте новый аккаунт
                </p>


            <?php endif; ?>


            <!-- MESSAGE -->

            <?php if ($message !== ""): ?>

                <div
                    class="message
                    <?php
                    echo $messageType;
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $message,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                    ?>

                </div>

            <?php endif; ?>


            <!-- =========================
                 LOGIN
            ========================= -->

            <?php if ($mode === "login"): ?>


                <form
                    method="POST"
                    id="loginForm"
                >


                    <input
                        type="hidden"
                        name="action"
                        value="login"
                    >


                    <div class="input-group">

                        <input
                            type="text"
                            name="username"
                            placeholder="Имя пользователя"
                            autocomplete="username"
                            required
                        >

                    </div>


                    <div class="input-group">

                        <input
                            type="password"
                            name="password"
                            placeholder="Пароль"
                            autocomplete="current-password"
                            required
                        >

                    </div>


                    <div class="input-group">

                        <input
                            type="number"
                            name="age"
                            placeholder="Возраст"
                            min="1"
                            max="120"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="auth-button"
                    >

                        Войти

                    </button>


                </form>


                <div class="auth-bottom">

                    Нет аккаунта?

                    <a href="log.php?mode=register">
                        Зарегистрироваться
                    </a>

                </div>


            <!-- =========================
                 REGISTER
            ========================= -->

            <?php else: ?>


                <form
                    method="POST"
                    id="registerForm"
                >


                    <input
                        type="hidden"
                        name="action"
                        value="register"
                    >


                    <div class="input-group">

                        <input
                            type="text"
                            name="username"
                            placeholder="Имя пользователя"
                            autocomplete="username"
                            minlength="3"
                            maxlength="50"
                            required
                        >

                    </div>


                    <div class="input-group">

                        <input
                            type="password"
                            name="password"
                            placeholder="Пароль"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="auth-button"
                    >

                        Зарегистрироваться

                    </button>


                </form>


                <div class="auth-bottom">

                    Уже есть аккаунт?

                    <a href="log.php">
                        Войти
                    </a>

                </div>


            <?php endif; ?>


        </section>


    </div>


</main>


<!-- =========================
     FOOTER
========================= -->

<footer>


    <div>

        © 2026

        <span>
            Kris
        </span>.

        My Little Media Library.

    </div>


    <div>

        made with music &amp; code ♫

    </div>


</footer>


<!-- =========================
     JAVASCRIPT
========================= -->

<script>


    const menuButton =
        document.getElementById(
            "menuButton"
        );


    const sidebar =
        document.getElementById(
            "sidebar"
        );


    const sidebarClose =
        document.getElementById(
            "sidebarClose"
        );


    const sidebarOverlay =
        document.getElementById(
            "sidebarOverlay"
        );


    function openMenu() {

        sidebar.classList.add(
            "active"
        );

        sidebarOverlay.classList.add(
            "active"
        );

        document.body.style.overflow =
            "hidden";
    }


    function closeMenu() {

        sidebar.classList.remove(
            "active"
        );

        sidebarOverlay.classList.remove(
            "active"
        );

        document.body.style.overflow =
            "";
    }


    menuButton.addEventListener(
        "click",
        openMenu
    );


    sidebarClose.addEventListener(
        "click",
        closeMenu
    );


    sidebarOverlay.addEventListener(
        "click",
        closeMenu
    );


    document.addEventListener(
        "keydown",
        function(event) {

            if (event.key === "Escape") {

                closeMenu();

            }

        }
    );


    /*
     * Проверка возраста
     */

    const loginForm =
        document.getElementById(
            "loginForm"
        );


    if (loginForm) {

        loginForm.addEventListener(
            "submit",
            function(event) {

                const age =
                    parseInt(
                        this.age.value,
                        10
                    );


                if (
                    isNaN(age) ||
                    age < 13
                ) {

                    event.preventDefault();


                    alert(
                        "Использование сайта разрешено пользователям от 13 лет."
                    );

                }

            }
        );

    }


</script>


</body>

</html>