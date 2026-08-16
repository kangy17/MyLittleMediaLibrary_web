<?php

session_start();

$isLoggedIn = isset($_SESSION['user_id']);


/* =========================
   ПОДКЛЮЧЕНИЕ К БД
========================= */

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


/* =========================
   AJAX: ИЗБРАННОЕ
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json; charset=utf-8');

    if (!isset($_SESSION['user_id'])) {

        echo json_encode([
            'success' => false,
            'message' => 'Необходимо войти в аккаунт.'
        ]);

        exit;
    }


    $user_id = $_SESSION['user_id'];

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );


    $band_id = isset($data['band_id'])
        ? intval($data['band_id'])
        : 0;

    $action = $data['action'] ?? '';


    if ($band_id <= 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Некорректная группа.'
        ]);

        exit;
    }


    /* Проверяем группу */

    $stmt = $conn->prepare(
        "SELECT id FROM bands WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $band_id
    );

    $stmt->execute();

    $result = $stmt->get_result();


    if ($result->num_rows === 0) {

        echo json_encode([
            'success' => false,
            'message' => 'Группа не найдена.'
        ]);

        exit;
    }

    $stmt->close();


    /* Добавление */

    if ($action === 'add') {

        $stmt = $conn->prepare(
            "INSERT IGNORE INTO favorites
             (user_id, band_id)
             VALUES (?, ?)"
        );

        $stmt->bind_param(
            "ii",
            $user_id,
            $band_id
        );

        $stmt->execute();

        $stmt->close();


        echo json_encode([
            'success' => true,
            'favorite' => true
        ]);

        exit;
    }


    /* Удаление */

    if ($action === 'remove') {

        $stmt = $conn->prepare(
            "DELETE FROM favorites
             WHERE user_id = ?
             AND band_id = ?"
        );

        $stmt->bind_param(
            "ii",
            $user_id,
            $band_id
        );

        $stmt->execute();

        $stmt->close();


        echo json_encode([
            'success' => true,
            'favorite' => false
        ]);

        exit;
    }


    echo json_encode([
        'success' => false,
        'message' => 'Неизвестное действие.'
    ]);

    exit;
}


/* =========================
   ПОЛУЧАЕМ ГРУППЫ
========================= */

$bands = [];

$query = "
    SELECT id, name, genre, image
    FROM bands
    ORDER BY id ASC
";

$result = $conn->query($query);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $bands[] = $row;
    }

}


/* =========================
   ПОЛУЧАЕМ ИЗБРАННЫЕ
========================= */

$favorites = [];

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "SELECT band_id
         FROM favorites
         WHERE user_id = ?"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {

        $favorites[] =
            (int)$row['band_id'];
    }


    $stmt->close();
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
        My Little Media / Группы
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


        /* =========================
           MENU BUTTON
        ========================= */

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


        /* =========================
           TOP NAVIGATION
        ========================= */

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

            transform:
                translateX(0);
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
                rgba(255,64,129,0.07);

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

            letter-spacing: 1px;
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
           MAIN
        ========================= */

        main {

            min-height: calc(100vh - 80px);

            padding:
                135px 7% 100px;
        }


        /* =========================
           PAGE INTRO
        ========================= */

        .page-intro {

            max-width: 1200px;

            margin: 0 auto 65px;

            display: flex;

            justify-content: space-between;

            align-items: end;

            gap: 40px;

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


        .intro-label {

            color: #ff4081;

            font-family: monospace;

            font-size: 13px;

            letter-spacing: 3px;

            margin-bottom: 15px;
        }


        .page-intro h1 {

            font-size:
                clamp(45px, 6vw, 78px);

            line-height: 0.95;

            letter-spacing: -4px;

            text-transform: uppercase;
        }


        .page-intro h1 span {

            color: #ff4081;

            text-shadow:
                0 0 30px
                rgba(255,64,129,0.3);
        }


        .intro-description {

            max-width: 420px;

            color: #777;

            font-size: 15px;

            line-height: 1.7;

            text-align: right;
        }


        /* =========================
           STAT LINE
        ========================= */

        .library-info {

            max-width: 1200px;

            margin: 0 auto 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding-bottom: 15px;

            border-bottom:
                1px solid
                rgba(255,255,255,0.08);
        }


        .library-info-left {

            color: #666;

            font-family: monospace;

            font-size: 12px;

            letter-spacing: 1px;
        }


        .library-info-left span {

            color: #ff4081;
        }


        .login-hint {

            color: #666;

            font-size: 12px;
        }


        /* =========================
           BANDS GRID
        ========================= */

        .bands-grid {

            max-width: 1200px;

            margin: 0 auto;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }


        /* =========================
           BAND CARD
        ========================= */

        .band-card {

            position: relative;

            background:
                rgba(17,17,20,0.88);

            border:
                1px solid #29292e;

            overflow: hidden;

            animation:
                cardAppear
                0.7s
                ease
                backwards;

            transition:
                transform 0.35s ease,
                border-color 0.35s ease,
                box-shadow 0.35s ease;
        }


        .band-card:nth-child(1) {
            animation-delay: 0.1s;
        }


        .band-card:nth-child(2) {
            animation-delay: 0.2s;
        }


        .band-card:nth-child(3) {
            animation-delay: 0.3s;
        }


        @keyframes cardAppear {

            from {

                opacity: 0;

                transform:
                    translateY(40px);
            }

            to {

                opacity: 1;

                transform:
                    translateY(0);
            }
        }


        .band-card:hover {

            transform:
                translateY(-8px);

            border-color:
                rgba(255,64,129,0.55);

            box-shadow:
                0 25px 60px
                rgba(0,0,0,0.55),
                0 0 35px
                rgba(255,64,129,0.08);
        }


        /* розовая линия сверху */

        .band-card::before {

            content: "";

            position: absolute;

            top: 0;
            left: 0;

            width: 0;

            height: 2px;

            background:
                linear-gradient(
                    90deg,
                    #ff4081,
                    #e040fb
                );

            z-index: 3;

            transition:
                width 0.4s ease;
        }


        .band-card:hover::before {

            width: 100%;
        }

/* =========================
   IMAGE MODAL
========================= */

.image-modal {

    position: fixed;

    inset: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 30px;

    background:
        rgba(0, 0, 0, 0.88);

    backdrop-filter:
        blur(8px);

    z-index: 2000;

    opacity: 0;

    visibility: hidden;

    transition:
        opacity 0.3s ease,
        visibility 0.3s ease;
}


.image-modal.active {

    opacity: 1;

    visibility: visible;
}


.image-modal img {

    max-width: 90vw;

    max-height: 90vh;

    width: auto;

    height: auto;

    object-fit: contain;

    border:
        1px solid
        rgba(255, 64, 129, 0.5);

    box-shadow:
        0 0 60px
        rgba(255, 64, 129, 0.15);

    transform:
        scale(0.9);

    transition:
        transform 0.3s ease;
}


.image-modal.active img {

    transform:
        scale(1);
}


.image-modal-close {

    position: absolute;

    top: 25px;

    right: 35px;

    width: 50px;

    height: 50px;

    display: flex;

    align-items: center;

    justify-content: center;

    border:
        1px solid
        rgba(255, 64, 129, 0.5);

    background:
        rgba(17, 17, 20, 0.8);

    color: #fff;

    font-size: 32px;

    line-height: 1;

    cursor: pointer;

    transition:
        0.3s;
}


.image-modal-close:hover {

    color: #ff4081;

    border-color:
        #ff4081;

    background:
        rgba(255, 64, 129, 0.1);

    transform:
        rotate(90deg);
}


@media (max-width: 650px) {

    .image-modal {

        padding: 15px;
    }


    .image-modal img {

        max-width: 95vw;

        max-height: 85vh;
    }


    .image-modal-close {

        top: 15px;

        right: 15px;

        width: 45px;

        height: 45px;

        font-size: 28px;
    }

}

        /* =========================
           IMAGE
        ========================= */

        .image-wrapper {

            position: relative;

            overflow: hidden;

            aspect-ratio: 1 / 1;

            background: #111114;
        }


        .band-image {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

    cursor: pointer;

    transition:
        transform 0.6s ease,
        filter 0.6s ease;
}

        .band-card:hover .band-image {

            transform:
                scale(1.06);

            filter:
                brightness(0.72);
        }


        .image-overlay {

    position: absolute;

    inset: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(9,9,11,0.35);

    opacity: 0;

    transition:
        opacity 0.3s ease;

    pointer-events: none;
}

        .band-card:hover .image-overlay {

            opacity: 1;
        }


        .image-overlay span {

            border:
                1px solid
                rgba(255,64,129,0.8);

            color: #fff;

            padding:
                10px 18px;

            background:
                rgba(255,64,129,0.15);

            backdrop-filter:
                blur(5px);

            font-size: 12px;

            letter-spacing: 2px;

            text-transform: uppercase;
        }


        /* =========================
           CARD CONTENT
        ========================= */

        .band-content {

            padding: 22px;
        }


        .band-number {

            color: #ff4081;

            font-family: monospace;

            font-size: 11px;

            letter-spacing: 2px;

            margin-bottom: 8px;
        }


        .band-name {

            font-size: 27px;

            line-height: 1.1;

            margin-bottom: 8px;

            color: #f2f2f2;
        }


        .band-genre {

            color: #777;

            font-size: 14px;

            line-height: 1.5;

            min-height: 21px;

            margin-bottom: 22px;
        }


        /* =========================
           FAVORITE BUTTON
        ========================= */

        .favorite-button {

            width: 100%;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 13px;

            border:
                1px solid #333;

            background:
                #111114;

            color: #aaa;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            transition:
                0.3s;
        }


        .favorite-button:hover {

            color: #fff;

            border-color:
                #ff4081;

            background:
                rgba(255,64,129,0.08);
        }


        .favorite-button.favorite {

            color: #fff;

            border-color:
                #ff4081;

            background:
                #ff4081;

            box-shadow:
                0 8px 25px
                rgba(255,64,129,0.15);
        }


        .favorite-button.favorite:hover {

            background: #e040fb;

            border-color: #e040fb;
        }


        .heart {

            font-size: 18px;

            line-height: 1;
        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty {

            grid-column: 1 / -1;

            text-align: center;

            padding: 100px 20px;

            border:
                1px dashed #333;

            color: #666;
        }


        .empty-symbol {

            color: #ff4081;

            font-size: 45px;

            margin-bottom: 15px;
        }


        .empty h2 {

            color: #aaa;

            margin-bottom: 8px;
        }


        .empty p {

            color: #555;

            font-size: 14px;
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

        @media (max-width: 950px) {

            .bands-grid {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .page-intro {

                flex-direction: column;

                align-items: flex-start;
            }


            .intro-description {

                text-align: left;

                max-width: 600px;
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


            main {

                padding:
                    115px 20px 70px;
            }


            .bands-grid {

                grid-template-columns: 1fr;
            }


            .page-intro h1 {

                font-size: 52px;

                letter-spacing: -3px;
            }


            .library-info {

                align-items: flex-start;

                flex-direction: column;

                gap: 8px;
            }


            .intro-description {

                font-size: 14px;
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
        id="sidebarClose"
        aria-label="Закрыть меню">

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


        <a
            href="bands.php"
            class="active">

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
     MAIN
========================= -->

<main>


    <section class="page-intro">

        <div>

            <div class="intro-label">
                // MUSIC DATABASE
            </div>

            <h1>
                Твои<br>
                <span>группы.</span>
            </h1>

        </div>


        <p class="intro-description">

            Исследуй музыкальные группы,
            открывай новые треки и сохраняй
            любимых исполнителей в своей
            медиатеке.

        </p>

    </section>


    <div class="library-info">

        <div class="library-info-left">

            GROUPS /
            <span>
                <?= count($bands) ?>
            </span>
            AVAILABLE

        </div>


        <div class="login-hint">

            <?php if ($isLoggedIn): ?>

                ♡ Добавляй группы в избранное

            <?php else: ?>

                Войдите, чтобы использовать избранное

            <?php endif; ?>

        </div>

    </div>


    <!-- =========================
         BANDS
    ========================= -->

    <section class="bands-grid">


        <?php if (empty($bands)): ?>


            <div class="empty">

                <div class="empty-symbol">
                    ♫
                </div>

                <h2>
                    Здесь пока тихо
                </h2>

                <p>
                    В базе данных пока нет музыкальных групп.
                </p>

            </div>


        <?php else: ?>


            <?php foreach ($bands as $index => $band): ?>


                <?php

                $isFavorite =
                    in_array(
                        (int)$band['id'],
                        $favorites,
                        true
                    );

                ?>


                <article
                    class="band-card"
                    data-band-id="<?= (int)$band['id'] ?>"
                >


                    <div class="image-wrapper">

                        <img
                            class="band-image"
                            src="<?= htmlspecialchars(
                                $band['image']
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $band['name']
                            ) ?>"
                        >


                        <div class="image-overlay">

                            <span>
                                My Little Media
                            </span>

                        </div>

                    </div>


                    <div class="band-content">


                        <div class="band-number">

                            BAND /
                            <?= str_pad(
                                $index + 1,
                                2,
                                '0',
                                STR_PAD_LEFT
                            ) ?>

                        </div>


                        <h2 class="band-name">

                            <?= htmlspecialchars(
                                $band['name']
                            ) ?>

                        </h2>


                        <div class="band-genre">

                            <?= htmlspecialchars(
                                $band['genre']
                            ) ?>

                        </div>


                        <button
                            class="favorite-button
                            <?= $isFavorite
                                ? 'favorite'
                                : ''
                            ?>"
                            data-favorite="
                                <?= $isFavorite
                                    ? 'true'
                                    : 'false'
                                ?>"
                        >

                            <span class="heart">

                                <?= $isFavorite
                                    ? '♥'
                                    : '♡'
                                ?>

                            </span>


                            <span class="favorite-text">

                                <?= $isFavorite
                                    ? 'В избранном'
                                    : 'В избранное'
                                ?>

                            </span>

                        </button>


                    </div>


                </article>


            <?php endforeach; ?>


        <?php endif; ?>


    </section>


</main>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div>

        © 2026
        <span>
            My little media library
        </span>

    </div>


    <div>

        Developed by
        <span>
            Kris
        </span>

    </div>

</footer>

<!-- =========================
     IMAGE MODAL
========================= -->

<div
    class="image-modal"
    id="imageModal"
>

    <button
        class="image-modal-close"
        id="imageModalClose"
        aria-label="Закрыть изображение"
    >
        ×
    </button>

    <img
        id="modalImage"
        src=""
        alt=""
    >

</div>

<script>

    /* =========================
   IMAGE MODAL
========================= */

const imageModal =
    document.getElementById(
        'imageModal'
    );


const modalImage =
    document.getElementById(
        'modalImage'
    );


const imageModalClose =
    document.getElementById(
        'imageModalClose'
    );


const bandImages =
    document.querySelectorAll(
        '.band-image'
    );


function openImageModal(image) {

    modalImage.src =
        image.src;

    modalImage.alt =
        image.alt;

    imageModal.classList.add(
        'active'
    );

    document.body.style.overflow =
        'hidden';
}


function closeImageModal() {

    imageModal.classList.remove(
        'active'
    );

    document.body.style.overflow =
        '';

    setTimeout(() => {

        modalImage.src = '';

    }, 300);
}


/* Клик по картинке */

bandImages.forEach(
    image => {

        image.addEventListener(
            'click',
            function() {

                openImageModal(
                    this
                );

            }
        );

    }
);


/* Кнопка X */

imageModalClose.addEventListener(
    'click',
    closeImageModal
);


/* Клик по затемненному фону */

imageModal.addEventListener(
    'click',
    function(event) {

        if (
            event.target === imageModal
        ) {

            closeImageModal();

        }

    }
);


/* Закрытие через ESC */

document.addEventListener(
    'keydown',
    function(event) {

        if (
            event.key === 'Escape' &&
            imageModal.classList.contains(
                'active'
            )
        ) {

            closeImageModal();

        }

    }
);


    /* =========================
       SIDEBAR
    ========================= */

    const menuButton =
        document.getElementById(
            'menuButton'
        );


    const sidebar =
        document.getElementById(
            'sidebar'
        );


    const sidebarClose =
        document.getElementById(
            'sidebarClose'
        );


    const sidebarOverlay =
        document.getElementById(
            'sidebarOverlay'
        );


    function openSidebar() {

        sidebar.classList.add(
            'active'
        );

        sidebarOverlay.classList.add(
            'active'
        );

        document.body.style.overflow =
            'hidden';
    }


    function closeSidebar() {

        sidebar.classList.remove(
            'active'
        );

        sidebarOverlay.classList.remove(
            'active'
        );

        document.body.style.overflow =
            '';
    }


    menuButton.addEventListener(
        'click',
        openSidebar
    );


    sidebarClose.addEventListener(
        'click',
        closeSidebar
    );


    sidebarOverlay.addEventListener(
        'click',
        closeSidebar
    );


    document.addEventListener(
        'keydown',
        function(event) {

            if (
                event.key === 'Escape'
            ) {

                closeSidebar();

            }

        }
    );


    /* =========================
       FAVORITES
    ========================= */

    const favoriteButtons =
        document.querySelectorAll(
            '.favorite-button'
        );


    favoriteButtons.forEach(
        button => {

            button.addEventListener(
                'click',
                async function() {


                    const card =
                        this.closest(
                            '.band-card'
                        );


                    const bandId =
                        card.dataset.bandId;


                    const isFavorite =
                        this.dataset.favorite
                        === 'true';


                    /*
                     * Временно блокируем кнопку,
                     * чтобы пользователь не устроил
                     * DDOS собственной медиатеке.
                     */

                    this.disabled = true;


                    try {


                        const response =
                            await fetch(
                                'bands.php',
                                {

                                    method:
                                        'POST',

                                    headers: {

                                        'Content-Type':
                                            'application/json'

                                    },

                                    body:
                                        JSON.stringify({

                                            band_id:
                                                Number(
                                                    bandId
                                                ),

                                            action:
                                                isFavorite
                                                    ? 'remove'
                                                    : 'add'

                                        })

                                }
                            );


                        const data =
                            await response.json();


                        if (!data.success) {

                            alert(
                                data.message ||
                                'Необходимо войти в аккаунт.'
                            );

                            return;
                        }


                        if (data.favorite) {


                            this.classList.add(
                                'favorite'
                            );


                            this.dataset.favorite =
                                'true';


                            this.querySelector(
                                '.heart'
                            ).textContent =
                                '♥';


                            this.querySelector(
                                '.favorite-text'
                            ).textContent =
                                'В избранном';


                        } else {


                            this.classList.remove(
                                'favorite'
                            );


                            this.dataset.favorite =
                                'false';


                            this.querySelector(
                                '.heart'
                            ).textContent =
                                '♡';


                            this.querySelector(
                                '.favorite-text'
                            ).textContent =
                                'В избранное';

                        }


                    } catch (error) {


                        console.error(
                            error
                        );


                        alert(
                            'Не удалось изменить избранное.'
                        );


                    } finally {

                        this.disabled = false;

                    }

                }
            );

        }
    );

</script>


</body>

</html>