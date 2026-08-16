<?php

session_start();

/* =========================================================
   ПРОВЕРКА АВТОРИЗАЦИИ
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: log.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];


/* =========================================================
   ПОДКЛЮЧЕНИЕ К БД
========================================================= */

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


/* =========================================================
   ПАПКА ДЛЯ АВАТАРОВ
========================================================= */

$avatarDir = __DIR__ . "/uploads/avatars/";
$avatarUrl = "uploads/avatars/";

if (!is_dir($avatarDir)) {
    mkdir($avatarDir, 0755, true);
}


/* =========================================================
   ПОЛУЧАЕМ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        username,
        pfp,
        created_at
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

$stmt->close();


if (!$user) {
    session_destroy();
    header("Location: log.php");
    exit;
}


/* =========================================================
   ЗАГРУЗКА АВАТАРА
========================================================= */

$message = "";
$messageType = "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['upload_avatar'])
) {

    if (
        !isset($_FILES['avatar'])
        ||
        $_FILES['avatar']['error'] !== UPLOAD_ERR_OK
    ) {

        $message = "Не удалось загрузить изображение.";
        $messageType = "error";

    } else {

        $file = $_FILES['avatar'];

        /* Максимальный размер: 5 MB */

        if ($file['size'] > 5 * 1024 * 1024) {

            $message = "Файл слишком большой. Максимум 5 МБ.";
            $messageType = "error";

        } else {

            $allowedTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            $mime = mime_content_type($file['tmp_name']);

            if (!isset($allowedTypes[$mime])) {

                $message = "Разрешены только JPG, PNG и WEBP.";
                $messageType = "error";

            } else {

                /*
                 * Удаляем старый аватар,
                 * если он существует.
                 */

                if (!empty($user['pfp'])) {

                    $oldFile = __DIR__ . "/" . $user['pfp'];

                    if (
                        file_exists($oldFile)
                        &&
                        is_file($oldFile)
                    ) {
                        unlink($oldFile);
                    }
                }


                /*
                 * Генерируем безопасное имя.
                 */

                $extension = $allowedTypes[$mime];

                $filename =
                    "avatar_" .
                    $user_id .
                    "_" .
                    bin2hex(random_bytes(8)) .
                    "." .
                    $extension;

                $destination =
                    $avatarDir . $filename;

                $relativePath =
                    $avatarUrl . $filename;


                if (
                    move_uploaded_file(
                        $file['tmp_name'],
                        $destination
                    )
                ) {

                    $stmt = $conn->prepare("
                        UPDATE users
                        SET pfp = ?
                        WHERE id = ?
                    ");

                    $stmt->bind_param(
                        "si",
                        $relativePath,
                        $user_id
                    );

                    $stmt->execute();
                    $stmt->close();


                    $user['pfp'] = $relativePath;

                    $message = "Аватар успешно обновлён.";
                    $messageType = "success";

                } else {

                    $message =
                        "Не удалось сохранить изображение.";

                    $messageType = "error";
                }
            }
        }
    }
}


/* =========================================================
   УДАЛЕНИЕ АВАТАРА
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['delete_avatar'])
) {

    if (!empty($user['pfp'])) {

        $oldFile =
            __DIR__ . "/" . $user['pfp'];

        if (
            file_exists($oldFile)
            &&
            is_file($oldFile)
        ) {
            unlink($oldFile);
        }
    }


    $stmt = $conn->prepare("
        UPDATE users
        SET pfp = NULL
        WHERE id = ?
    ");

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();
    $stmt->close();


    $user['pfp'] = null;

    $message = "Аватар удалён.";
    $messageType = "success";
}


/* =========================================================
   УДАЛЕНИЕ ИЗ ИЗБРАННОГО
========================================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    &&
    isset($_POST['remove_favorite'])
) {

    $band_id =
        intval($_POST['band_id'] ?? 0);

    if ($band_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM favorites
            WHERE user_id = ?
            AND band_id = ?
        ");

        $stmt->bind_param(
            "ii",
            $user_id,
            $band_id
        );

        $stmt->execute();
        $stmt->close();

        $message = "Группа удалена из избранного.";
        $messageType = "success";
    }
}


/* =========================================================
   ПОЛУЧАЕМ ИЗБРАННЫЕ ГРУППЫ
========================================================= */

$favorites = [];

$stmt = $conn->prepare("
    SELECT
        bands.id,
        bands.name,
        bands.genre,
        bands.image
    FROM favorites

    INNER JOIN bands
        ON bands.id = favorites.band_id

    WHERE favorites.user_id = ?

    ORDER BY bands.name ASC
");

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

$stmt->close();


/* =========================================================
   ДАТА РЕГИСТРАЦИИ
========================================================= */

$createdDate = "";

if (!empty($user['created_at'])) {

    $createdDate = date(
        "d.m.Y",
        strtotime($user['created_at'])
    );
}

?>

<!DOCTYPE html>
<html lang="ru">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Моя медиатека / Избранное</title>


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


/* =========================================================
   GRID
========================================================= */

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


/* =========================================================
   HEADER
========================================================= */

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


/* =========================================================
   SIDEBAR
========================================================= */

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


.sidebar-links a.active {

    color: #ff4081;

    background:
        rgba(255,64,129,0.08);

    border-left-color:
        #ff4081;
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


/* =========================================================
   PAGE
========================================================= */

.page {

    max-width: 1250px;

    margin: auto;

    padding:
        130px 30px 80px;

    animation:
        fadeUp
        0.8s ease;
}


@keyframes fadeUp {

    from {

        opacity: 0;

        transform:
            translateY(30px);
    }

    to {

        opacity: 1;

        transform:
            translateY(0);
    }
}


.page-header {

    margin-bottom: 45px;
}


.eyebrow {

    color: #ff4081;

    font-family: monospace;

    font-size: 12px;

    letter-spacing: 3px;

    margin-bottom: 12px;
}


.page-header h1 {

    font-size: 56px;

    line-height: 1;

    letter-spacing: -2px;

    margin-bottom: 15px;
}


.page-header h1 span {
    color: #ff4081;
}


.page-header p {

    color: #777;

    font-size: 16px;
}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 14px 18px;

    margin-bottom: 30px;

    font-size: 13px;

    border-left: 3px solid;
}


.message.success {

    color: #78e08f;

    background:
        rgba(120,224,143,0.06);

    border-color:
        #78e08f;
}


.message.error {

    color: #ff6b9d;

    background:
        rgba(255,64,129,0.06);

    border-color:
        #ff4081;
}


/* =========================================================
   DASHBOARD
========================================================= */

.dashboard {

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        360px;

    gap: 35px;

    align-items: start;
}


/* =========================================================
   FAVORITES
========================================================= */

.favorites-panel {

    background:
        rgba(16,16,19,0.82);

    border:
        1px solid #27272d;

    min-height: 430px;

    padding: 28px;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,0.25);
}


.panel-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding-bottom: 22px;

    margin-bottom: 20px;

    border-bottom:
        1px solid #27272d;
}


.panel-header h2 {

    font-size: 23px;
}


.count {

    color: #ff4081;

    font-family: monospace;

    font-size: 13px;

    padding: 5px 9px;

    border:
        1px solid
        rgba(255,64,129,0.35);
}


.favorite-list {

    display: flex;

    flex-direction: column;

    gap: 12px;
}


.favorite-item {

    display: grid;

    grid-template-columns:
        70px 1fr auto;

    gap: 18px;

    align-items: center;

    padding: 12px;

    background: #111114;

    border:
        1px solid #25252b;

    transition: 0.3s;

    animation:
        itemAppear
        0.5s ease backwards;
}


.favorite-item:hover {

    border-color:
        rgba(255,64,129,0.5);

    transform:
        translateX(4px);
}


@keyframes itemAppear {

    from {

        opacity: 0;

        transform:
            translateX(-15px);
    }

    to {

        opacity: 1;

        transform:
            translateX(0);
    }
}


.favorite-image {

    width: 70px;
    height: 70px;

    object-fit: cover;

    border-radius: 3px;
}


.favorite-info h3 {

    font-size: 18px;

    margin-bottom: 5px;
}


.favorite-info span {

    color: #777;

    font-size: 13px;
}


.remove-button {

    width: 38px;
    height: 38px;

    border:
        1px solid #333;

    background: transparent;

    color: #777;

    cursor: pointer;

    font-size: 18px;

    transition: 0.3s;
}


.remove-button:hover {

    color: white;

    background: #ff4081;

    border-color:
        #ff4081;

    transform:
        rotate(8deg);
}


/* =========================================================
   EMPTY
========================================================= */

.empty-state {

    min-height: 300px;

    display: flex;

    flex-direction: column;

    justify-content: center;

    align-items: center;

    text-align: center;

    color: #666;
}


.empty-icon {

    font-size: 55px;

    color: #ff4081;

    margin-bottom: 20px;
}


.empty-state h3 {

    color: #aaa;

    font-size: 21px;

    margin-bottom: 8px;
}


.empty-state p {

    font-size: 14px;

    max-width: 350px;

    line-height: 1.6;
}


.empty-state a {

    display: inline-block;

    margin-top: 20px;

    color: white;

    background: #ff4081;

    text-decoration: none;

    padding: 11px 22px;

    font-size: 14px;

    transition: 0.3s;
}


.empty-state a:hover {

    background: #e040fb;

    transform:
        translateY(-2px);
}


/* =========================================================
   PROFILE
========================================================= */

.profile-card {

    background:
        rgba(16,16,19,0.92);

    border:
        1px solid #29292e;

    padding: 30px;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 25px 70px
        rgba(0,0,0,0.3);
}


.profile-card::before {

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


.profile-heading {

    color: #777;

    font-family: monospace;

    font-size: 12px;

    letter-spacing: 2px;

    margin-bottom: 25px;
}


.avatar-area {

    text-align: center;

    padding-bottom: 25px;

    border-bottom:
        1px solid #29292e;
}


.avatar {

    width: 135px;
    height: 135px;

    margin: auto;

    border-radius: 50%;

    object-fit: cover;

    border:
        2px solid
        rgba(255,64,129,0.6);

    box-shadow:
        0 0 35px
        rgba(255,64,129,0.12);
}


.avatar-placeholder {

    width: 135px;
    height: 135px;

    margin: auto;

    border-radius: 50%;

    border:
        1px dashed
        rgba(255,64,129,0.5);

    display: flex;

    justify-content: center;

    align-items: center;

    color: #ff4081;

    font-size: 42px;

    background:
        rgba(255,64,129,0.04);
}


.profile-name {

    margin-top: 18px;

    font-size: 24px;

    font-weight: 700;
}


.profile-label {

    color: #666;

    font-size: 12px;

    margin-top: 5px;
}


.avatar-actions {

    margin-top: 22px;

    display: flex;

    flex-direction: column;

    gap: 9px;
}


.avatar-button {

    display: block;

    width: 100%;

    padding: 11px;

    border:
        1px solid #333;

    background: #18181b;

    color: #ccc;

    cursor: pointer;

    font-size: 13px;

    transition: 0.3s;
}


.avatar-button:hover {

    border-color: #ff4081;

    color: #ff4081;
}


.avatar-button.primary {

    background: #ff4081;

    border-color: #ff4081;

    color: white;
}


.avatar-button.primary:hover {

    background: #e040fb;

    color: white;
}


.avatar-button.danger:hover {

    background: #ff4081;

    color: white;
}


/* =========================================================
   PROFILE INFO
========================================================= */

.profile-info {

    padding-top: 25px;
}


.info-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 13px 0;

    border-bottom:
        1px solid
        rgba(255,255,255,0.05);
}


.info-row:last-child {
    border-bottom: none;
}


.info-row span:first-child {

    color: #666;

    font-size: 13px;
}


.info-row span:last-child {

    color: #ddd;

    font-size: 13px;

    text-align: right;
}


.profile-status {

    color: #78e08f !important;
}


/* =========================================================
   FOOTER
========================================================= */

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

    margin-top: 30px;
}


footer span {
    color: #ff4081;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 850px) {

    .dashboard {

        grid-template-columns: 1fr;
    }


    .profile-card {

        max-width: none;
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


    .page {

        padding:
            110px 20px 60px;
    }


    .page-header h1 {

        font-size: 43px;
    }


    .favorites-panel,
    .profile-card {

        padding: 20px;
    }


    .favorite-item {

        grid-template-columns:
            55px 1fr auto;

        gap: 12px;
    }


    .favorite-image {

        width: 55px;
        height: 55px;
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


<!-- =========================================================
     SIDEBAR
========================================================= -->

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


        <a href="favorites.php"
           class="active">

            <span class="sidebar-icon">
                ♡
            </span>

            Избранное

        </a>

    </nav>

</aside>


<!-- =========================================================
     HEADER
========================================================= -->

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


<!-- =========================================================
     PAGE
========================================================= -->

<main class="page">


    <section class="page-header">

        <div class="eyebrow">
            // PERSONAL SPACE
        </div>

        <h1>
            Моё <span>избранное</span>
        </h1>

        <p>
            Твоя музыка, твои группы, твоя маленькая цифровая берлога.
        </p>

    </section>


    <?php if (!empty($message)): ?>

        <div class="message <?= $messageType ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <div class="dashboard">


        <!-- =================================================
             FAVORITES
        ================================================= -->

        <section class="favorites-panel">


            <div class="panel-header">

                <h2>
                    Любимые группы
                </h2>

                <span class="count">

                    <?= count($favorites) ?>

                </span>

            </div>


            <?php if (empty($favorites)): ?>


                <div class="empty-state">

                    <div class="empty-icon">
                        ♡
                    </div>

                    <h3>
                        Здесь пока пусто
                    </h3>

                    <p>
                        Ты ещё не добавил ни одной группы.
                        Видимо, музыкальная гражданская война
                        пока не началась.
                    </p>

                    <a href="bands.php">
                        Открыть группы
                    </a>

                </div>


            <?php else: ?>


                <div class="favorite-list">


                    <?php foreach ($favorites as $index => $band): ?>


                        <article
                            class="favorite-item"
                            style="
                                animation-delay:
                                <?= $index * 0.05 ?>s;
                            ">


                            <img
                                class="favorite-image"

                                src="<?= htmlspecialchars(
                                    $band['image']
                                ) ?>"

                                alt="<?= htmlspecialchars(
                                    $band['name']
                                ) ?>"
                            >


                            <div class="favorite-info">

                                <h3>

                                    <?= htmlspecialchars(
                                        $band['name']
                                    ) ?>

                                </h3>

                                <span>

                                    <?= htmlspecialchars(
                                        $band['genre']
                                    ) ?>

                                </span>

                            </div>


                            <form method="post">

                                <input
                                    type="hidden"
                                    name="band_id"
                                    value="<?= (int)$band['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    name="remove_favorite"
                                    class="remove-button"
                                    title="Удалить из избранного">

                                    ×

                                </button>

                            </form>


                        </article>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>


        <!-- =================================================
             PROFILE
        ================================================= -->

        <aside class="profile-card">


            <div class="profile-heading">
                // PROFILE
            </div>


            <div class="avatar-area">


                <?php if (!empty($user['pfp'])): ?>


                    <img
                        class="avatar"

                        src="<?= htmlspecialchars(
                            $user['pfp']
                        ) ?>"

                        alt="Аватар пользователя"
                    >


                <?php else: ?>


                    <div class="avatar-placeholder">
                        ♡
                    </div>


                <?php endif; ?>


                <div class="profile-name">

                    <?= htmlspecialchars(
                        $user['username']
                    ) ?>

                </div>


                <div class="profile-label">
                    USER
                </div>


                <div class="avatar-actions">


                    <form
                        method="post"
                        enctype="multipart/form-data"
                    >

                        <label
                            class="avatar-button primary"
                            for="avatarInput">

                            <?= empty($user['pfp'])
                                ? 'Загрузить аватар'
                                : 'Изменить аватар'
                            ?>

                        </label>


                        <input
                            type="file"
                            id="avatarInput"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp"
                            style="display:none"
                            onchange="this.form.submit()"
                        >


                        <input
                            type="hidden"
                            name="upload_avatar"
                            value="1"
                        >

                    </form>


                    <?php if (!empty($user['pfp'])): ?>

                        <form method="post">

                            <button
                                type="submit"
                                name="delete_avatar"
                                class="avatar-button danger">

                                Удалить аватар

                            </button>

                        </form>

                    <?php endif; ?>


                </div>

            </div>


            <div class="profile-info">


                <div class="info-row">

                    <span>
                        Имя пользователя
                    </span>

                    <span>
                        <?= htmlspecialchars(
                            $user['username']
                        ) ?>
                    </span>

                </div>


                <div class="info-row">

                    <span>
                        На сайте с
                    </span>

                    <span>
                        <?= htmlspecialchars(
                            $createdDate
                        ) ?>
                    </span>

                </div>


                <div class="info-row">

                    <span>
                        Избранных групп
                    </span>

                    <span>
                        <?= count($favorites) ?>
                    </span>

                </div>


                <div class="info-row">

                    <span>
                        Статус
                    </span>

                    <span class="profile-status">
                        ● ACTIVE
                    </span>

                </div>


            </div>


        </aside>


    </div>


</main>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    <div>

        © 2026
        <span>My little media library</span>

    </div>


    <div>

        Developed by
        <span>Kris</span>

    </div>

</footer>


<script>

/* =========================================================
   SIDEBAR
========================================================= */

const menuButton =
    document.getElementById("menuButton");

const sidebar =
    document.getElementById("sidebar");

const sidebarOverlay =
    document.getElementById("sidebarOverlay");

const sidebarClose =
    document.getElementById("sidebarClose");


function openSidebar() {

    sidebar.classList.add("active");

    sidebarOverlay.classList.add("active");

}


function closeSidebar() {

    sidebar.classList.remove("active");

    sidebarOverlay.classList.remove("active");

}


menuButton.addEventListener(
    "click",
    openSidebar
);


sidebarClose.addEventListener(
    "click",
    closeSidebar
);


sidebarOverlay.addEventListener(
    "click",
    closeSidebar
);


/* =========================================================
   ESC
========================================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            closeSidebar();

        }

    }
);

</script>


</body>
</html>