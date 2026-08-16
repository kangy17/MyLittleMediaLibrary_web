<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Моя медиатека / Контакты</title>

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
            min-height: 100vh;

            font-family:
                "Segoe UI",
                Tahoma,
                Geneva,
                Verdana,
                sans-serif;

            background: #09090b;
            color: #f2f2f2;

            overflow-x: hidden;
        }

        body::before {
            content: "";

            position: fixed;
            inset: 0;

            pointer-events: none;

            z-index: 1000;

            opacity: .08;

            background-image:
                linear-gradient(
                    rgba(255, 64, 129, .08) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 64, 129, .08) 1px,
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

            background: rgba(10, 10, 12, .88);

            backdrop-filter: blur(15px);

            border-bottom:
                1px solid
                rgba(255, 64, 129, .15);

            z-index: 900;

            animation:
                headerAppear .7s ease;
        }


        @keyframes headerAppear {

            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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

            align-items: center;
            justify-content: center;

            gap: 5px;

            background:
                rgba(255, 64, 129, .05);

            border:
                1px solid
                rgba(255, 64, 129, .4);

            cursor: pointer;

            transition: .3s;
        }


        .menu-button span {
            width: 20px;
            height: 2px;

            background: #ff4081;

            transition: .3s;
        }


        .menu-button:hover {
            background: #ff4081;

            box-shadow:
                0 0 25px
                rgba(255, 64, 129, .4);
        }


        .menu-button:hover span {
            background: #09090b;
        }


        .developer {
            color: #b5b5b5;

            font-size: 15px;

            letter-spacing: 1px;
        }


        .developer strong {
            color: #ff4081;

            font-weight: 600;
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

            transition: .3s;
        }


        .top-navigation a::after {
            content: "";

            position: absolute;

            left: 0;
            bottom: -8px;

            width: 0;
            height: 2px;

            background: #ff4081;

            transition: .3s;
        }


        .top-navigation a:hover,
        .top-navigation a.active {
            color: #ff4081;
        }


        .top-navigation a:hover::after,
        .top-navigation a.active::after {
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

            padding: 35px 25px;

            background:
                linear-gradient(
                    180deg,
                    #111116 0%,
                    #09090b 100%
                );

            border-right:
                1px solid
                rgba(255, 64, 129, .25);

            z-index: 1100;

            transform: translateX(-100%);

            transition:
                transform
                .45s cubic-bezier(.77, 0, .18, 1);
        }


        .sidebar.active {
            transform: translateX(0);
        }


        .sidebar-logo {
            margin-bottom: 45px;

            padding-bottom: 25px;

            border-bottom:
                1px solid
                rgba(255,255,255,.08);
        }


        .sidebar-logo .symbol {
            color: #ff4081;

            font-size: 25px;
            font-weight: bold;
        }


        .sidebar-logo h2 {
            margin-top: 8px;

            font-size: 20px;
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

            color: #888;

            background: transparent;

            border: none;

            font-size: 25px;

            cursor: pointer;

            transition: .3s;
        }


        .sidebar-close:hover {
            color: #ff4081;

            transform: rotate(90deg);
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

            transition: .3s;
        }


        .sidebar-links a:hover,
        .sidebar-links a.active {
            color: #ff4081;

            background:
                rgba(255,64,129,.07);

            border-left-color:
                #ff4081;

            padding-left: 22px;
        }


        .sidebar-icon {
            width: 25px;

            color: #ff4081;

            font-size: 17px;

            text-align: center;
        }


        .beta {
            margin-left: auto;

            padding: 3px 6px;

            color: #ff4081;

            border:
                1px solid #ff4081;

            font-size: 9px;
        }


        .sidebar-overlay {
            position: fixed;

            inset: 0;

            z-index: 1050;

            background:
                rgba(0,0,0,.65);

            backdrop-filter: blur(4px);

            opacity: 0;
            visibility: hidden;

            transition: .3s;
        }


        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }


        /* =========================
           MAIN
        ========================= */

        main {
            position: relative;

            min-height:
                calc(100vh - 76px);

            padding:
                135px 8% 90px;

            overflow: hidden;
        }


        main::before {
            content: "";

            position: absolute;

            width: 550px;
            height: 550px;

            top: 50px;
            right: -200px;

            border-radius: 50%;

            background:
                rgba(255,64,129,.07);

            filter: blur(100px);

            pointer-events: none;
        }


        .contact-container {
            position: relative;

            z-index: 2;

            max-width: 1400px;

            margin: 0 auto;
        }


        /* =========================
           INTRO
        ========================= */

        .contact-intro {
            max-width: 800px;

            margin-bottom: 55px;

            animation:
                slideLeft .8s ease;
        }


        @keyframes slideLeft {

            from {
                opacity: 0;

                transform:
                    translateX(-50px);
            }

            to {
                opacity: 1;

                transform:
                    translateX(0);
            }

        }


        .eyebrow {
            margin-bottom: 18px;

            color: #ff4081;

            font-family: monospace;

            font-size: 13px;

            letter-spacing: 3px;
        }


        .contact-intro h1 {
            margin-bottom: 25px;

            font-size:
                clamp(55px, 8vw, 110px);

            line-height: .88;

            letter-spacing: -6px;

            text-transform: uppercase;
        }


        .contact-intro h1 span {
            color: #ff4081;

            text-shadow:
                0 0 25px
                rgba(255,64,129,.3);
        }


        .contact-intro p {
            max-width: 700px;

            color: #999;

            font-size: 18px;

            line-height: 1.7;
        }


        /* =========================
           GRID
        ========================= */

        .contact-grid {
            display: grid;

            grid-template-columns:
                .75fr 1.25fr;

            gap: 30px;

            animation:
                fadeUp .9s ease;
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
           LEFT CARDS
        ========================= */

        .contact-info {
            display: flex;

            flex-direction: column;

            gap: 14px;
        }


        .info-card {
            padding: 24px;

            background:
                linear-gradient(
                    135deg,
                    rgba(255,255,255,.045),
                    rgba(255,255,255,.015)
                );

            border:
                1px solid
                rgba(255,255,255,.08);

            transition: .3s;
        }


        .info-card:hover {
            border-color:
                rgba(255,64,129,.4);

            transform:
                translateX(6px);

            background:
                rgba(255,64,129,.04);
        }


        .info-number {
            margin-bottom: 15px;

            color: #ff4081;

            font-family: monospace;

            font-size: 12px;

            letter-spacing: 2px;
        }


        .info-card h3 {
            margin-bottom: 7px;

            font-size: 19px;
        }


        .info-card p {
            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        .info-card a {
            display: inline-block;

            margin-top: 10px;

            color: #ff4081;

            text-decoration: none;

            font-size: 13px;
        }


        .info-card a:hover {
            text-decoration: underline;
        }


        /* =========================
           FORM
        ========================= */

        .form-card {
            padding: 35px;

            background:
                linear-gradient(
                    145deg,
                    #151519,
                    #0f0f12
                );

            border:
                1px solid
                rgba(255,255,255,.09);

            box-shadow:
                0 25px 70px
                rgba(0,0,0,.35);
        }


        .form-header {
            margin-bottom: 30px;
        }


        .form-header h2 {
            margin-bottom: 8px;

            font-size: 30px;
        }


        .form-header p {
            color: #777;

            font-size: 13px;

            line-height: 1.6;
        }


        .form-group {
            margin-bottom: 20px;
        }


        .form-group label {
            display: block;

            margin-bottom: 8px;

            color: #ccc;

            font-size: 13px;
        }


        .form-group input,
        .form-group textarea {
            width: 100%;

            padding: 14px 15px;

            color: #f2f2f2;

            background:
                rgba(255,255,255,.035);

            border:
                1px solid
                rgba(255,255,255,.1);

            outline: none;

            font-size: 14px;

            transition: .25s;
        }


        .form-group input {
            height: 50px;
        }


        .form-group textarea {
            min-height: 170px;

            resize: vertical;
        }


        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #ff4081;

            background:
                rgba(255,64,129,.035);

            box-shadow:
                0 0 0 3px
                rgba(255,64,129,.07);
        }


        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #555;
        }


        .submit-button {
            width: 100%;

            display: flex;

            align-items: center;
            justify-content: space-between;

            padding: 16px 20px;

            color: #09090b;

            background: #ff4081;

            border: none;

            font-size: 14px;
            font-weight: 700;

            cursor: pointer;

            transition: .3s;
        }


        .submit-button:hover {
            background: #ff6096;

            box-shadow:
                0 0 30px
                rgba(255,64,129,.25);
        }


        .submit-arrow {
            font-size: 18px;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {
            display: flex;

            align-items: center;
            justify-content: space-between;

            padding:
                30px 8%;

            color: #666;

            font-size: 13px;

            border-top:
                1px solid
                rgba(255,255,255,.07);
        }


        footer span {
            color: #ff4081;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 900px) {

            .contact-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 700px) {

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
                    125px 25px 70px;
            }


            .contact-intro h1 {
                font-size: 58px;

                letter-spacing: -3px;
            }


            .contact-intro p {
                font-size: 16px;
            }


            .form-card {
                padding: 25px 20px;
            }


            footer {
                flex-direction: column;

                align-items: flex-start;

                gap: 10px;

                padding:
                    25px;
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


        <a
            href="kont.php"
            class="active"
        >

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

        <a
            href="kont.php"
            class="active"
        >
            Контакты
        </a>

    </nav>

</header>


<!-- =========================
     MAIN
========================= -->

<main>

    <div class="contact-container">


        <section class="contact-intro">

    <div class="eyebrow">
        // СВЯЗАТЬСЯ
    </div>

    <h1>
        HAVE A
        <br>
        <span>MESSAGE?</span>
    </h1>

    <p>
        Если у вас есть вопрос, предложение или отзыв
        о проекте, воспользуйтесь формой обратной связи.
        Мы постараемся ответить на ваше сообщение.
    </p>

</section>


        <section class="contact-grid">


            <!-- LEFT -->

            <div class="contact-info">

    <div class="info-card">

        <div class="info-number">
            01 / PROJECT
        </div>

        <h3>
            Вопросы о сайте
        </h3>

        <p>
            Сообщения об ошибках, предложения по интерфейсу,
            проблемы с медиатекой и отдельными функциями сайта.
        </p>

    </div>


    <div class="info-card">

        <div class="info-number">
            02 / FEEDBACK
        </div>

        <h3>
            Обратная связь
        </h3>

        <p>
            Отзывы о проекте и предложения по добавлению
            новых возможностей.
        </p>

    </div>


    <div class="info-card">

        <div class="info-number">
            03 / MUSIC
        </div>

        <h3>
            Музыкальная библиотека
        </h3>

        <p>
            Если в медиатеке отсутствует нужная группа,
            исполнитель или альбом, сообщите об этом
            через форму обратной связи.
        </p>

    </div>


    <div class="info-card">

        <div class="info-number">
            04 / CONTACT
        </div>

        <h3>
            Связь с разработчиком
        </h3>

        <p>
            По вопросам, связанным с работой и развитием
            проекта, используйте форму ниже.
        </p>

    </div>

</div>

            <!-- RIGHT -->

            <div class="form-card">

                <div class="form-header">

    <div class="eyebrow">
        // MESSAGE
    </div>

    <h2>
        Напишите нам
    </h2>

    <p>
        Заполните форму ниже, чтобы отправить сообщение.
        Все поля обязательны для заполнения.
    </p>

</div>


                <form
                    id="contactForm"
                    action="kontdb/users.php"
                    method="post"
                >


                    <div class="form-group">

                        <label for="name">
                            Ваше имя
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            placeholder="Введите имя"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            placeholder="example@mail.com"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="message">
                            Сообщение
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            placeholder="Напишите ваше сообщение..."
                            required
                        ></textarea>

                    </div>


                    <button
                        type="submit"
                        class="submit-button"
                    >

                        <span>
                            ОТПРАВИТЬ СООБЩЕНИЕ
                        </span>

                        <span class="submit-arrow">
                            →
                        </span>

                    </button>

                </form>

            </div>

        </section>

    </div>

</main>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <div>

        © 2026
        <span>Kris</span>.
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
        event => {

            if (
                event.key === "Escape"
            ) {

                closeMenu();

            }

        }
    );

</script>


</body>
</html>