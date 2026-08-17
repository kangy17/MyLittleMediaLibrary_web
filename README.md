# MyLittleMediaLibrary_web
My Little Media Library — это веб-приложение для хранения и просмотра музыкальной медиатеки.
Проект позволяет пользователям просматривать исполнителей и альбомы, добавлять понравившийся контент в избранное и работать с личным аккаунтом.

> ⚠️ **Обратите внимание:** Данный репозиторий содержит пример с конфигурацией под **локальную базу данных** (для запуска на XAMPP / OpenServer).  
> 🌐 Рабочая версия сайта с подключенной серверной БД доступна по ссылке: **[Открыть рабочий сайт](http://kangy.free.je)**
> 
***HTML + CSS + JavaScript + PHP + MySQL.***

* 💜 Небольшая музыкальная библиотека с собственным дизайном и системой пользователей.
* ✨ Возможности
* 🎧 Просмотр музыкальной библиотеки
* 🎤 Каталог исполнителей и групп
* 💿 Страницы альбомов
* ❤️ Добавление контента в избранное
* 👤 Регистрация и авторизация пользователей
* 🔐 Сессии пользователей
* 🚪 Выход из аккаунта
* 🖼 Пользовательские аватары
* 🗄 Хранение данных в MySQL
* 📱 Адаптивный интерфейс

## 🛠 Технологии
### Frontend
* HTML5
* CSS3
* JavaScript
### Backend
* PHP
* MySQL
* PHP Sessions
### Сервер
Для локального запуска можно использовать:
* XAMPP
* OpenServer
* Denwer
* любой другой сервер с поддержкой PHP + MySQL

## 📁 Структура проекта

medialibrary/<br>
│<br>
├── index.html          # Главная страница<br>
├── info.html           # Информация о проекте<br>
│<br>
├── bands.php           # Исполнители / группы<br>
├── favorites.php       # Избранное пользователя<br>
├── kont.php            # Форма обратной связи<br>
├── log.php             # Авторизация<br>
├── logout.php          # Выход из аккаунта<br>
│<br>
├── lp.html              # Страница Linkin Park<br>
├── mcr.html             # Страница My Chemical Romance<br>
├── msi.html             # Страница Mindless Self Indulgence<br>
│<br>
├── bg1.png              # Фоновое изображение<br>
│<br>
├── uploads/<br>
│   └── avatars/         # Пользовательские аватары<br>
│<br>
├── kontdb/<br>
│   └── users.php        # Работа с сообщениями пользователей<br>
│<br>
└── .htaccess            # Настройки Apache<br>

## 🔐 Авторизация
В проекте реализована система пользователей.
Пользователь может:
* Создать аккаунт.
* Войти в систему.
* Использовать персональное избранное.
* Загрузить аватар.
* Выйти из аккаунта.

Для хранения состояния авторизации используются PHP Sessions.

## ❤️ Избранное
Авторизованные пользователи могут сохранять понравившиеся элементы медиатеки в разделе Favorites.
Избранное является персональным и связано с аккаунтом пользователя.
## 🗄 База данных

Проект использует СУБД MySQL для хранения данных. Перед запуском приложения необходимо создать базу данных, развернуть таблицы и настроить подключение в PHP-файлах проекта.

### Параметры подключения по умолчанию:
```php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "kont_db";
```

---

## 🚀 Запуск проекта

### Шаг 1. Подготовка локального сервера
Установите и запустите локальный сервер с поддержкой PHP и MySQL. Отлично подойдут:
* **XAMPP** ([официальный сайт](https://www.apachefriends.org/))
* **OpenServer Panel**

### Шаг 2. Размещение файлов проекта
Перенесите папку с проектом в корневую директорию вашего веб-сервера. 
* Для **XAMPP** путь должен выглядеть так:
  ```text
  xampp/htdocs/medialibrary/
  ```

### Шаг 3. Запуск веб-служб
Откройте панель управления вашего сервера (например, XAMPP Control Panel) и запустите модули **Apache** и **MySQL**. Убедитесь, что индикаторы загорелись зеленым цветом.

### Шаг 4. Настройка базы данных и таблиц
1. Откройте инструмент управления БД (например, **phpMyAdmin** по адресу `http://localhost/phpmyadmin/`).
2. Перейдите во вкладку **SQL**.
3. Скопируйте, вставьте и выполните единый скрипт инициализации, приведенный ниже. Он автоматически создаст базу данных `kont_db` и все 4 необходимые таблицы (`users`, `bands`, `messages`, `favorites`):

```sql
-- 1. Создание базы данных (если она еще не создана)
CREATE DATABASE IF NOT EXISTS kont_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Переключение на созданную базу данных
USE kont_db;

-- 2. Создание таблицы пользователей (users)
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    pfp VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Создание таблицы музыкальных групп (bands)
CREATE TABLE IF NOT EXISTS bands (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    image VARCHAR(500) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Создание таблицы сообщений обратной связи (messages)
CREATE TABLE IF NOT EXISTS messages (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) DEFAULT NULL,
    email VARCHAR(50) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Создание таблицы избранного (favorites) с составным первичным ключом
CREATE TABLE IF NOT EXISTS favorites (
    user_id INT NOT NULL,
    band_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, band_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `bands` (`id`, `name`, `genre`, `image`, `created_at`) VALUES
(1, 'Slipknot', 'Ню-метал', 'https://i.scdn.co/image/ab67616100005174ff9f4de8c13f6f563acbfaf1', '2026-08-16 05:57:08'),
(2, 'Mindless Self Indulgence', 'Электропанк / Индастриал', 'https://www.toledoblade.com/image/2008/10/30/1140x_a10-7_cTC/Mindless-Self-Indulgence-works-hard-at-being-original.jpg', '2026-08-16 05:57:08'),
(3, 'System Of A Down', 'Ню-метал', 'https://i.scdn.co/image/ab6761610000e5eb60063d3451ade8f9fab397c2', '2026-08-16 05:57:08'),
(4, 'Linkin Park', 'Ню-метал', 'https://www.metalzone.fr/wp-content/uploads/2020/03/linkin-park-1200x764.jpg', '2026-08-16 05:57:08'),
(5, 'Deftones', 'Ню-метал', 'https://www.nme.com/wp-content/uploads/2025/08/Deftones_private_music_review_red_2000-696x442.jpg', '2026-08-16 05:57:08'),
(6, 'My Chemical Romance', 'Эмо-рок', 'https://www.cultura.id/wp-content/uploads/2019/12/my-chemical-romance.jpg', '2026-08-16 05:57:08'),
(7, 'Green Day', 'Панк-рок', 'https://pbs.twimg.com/media/GkQLX0-XcAEvLTC.jpg', '2026-08-16 05:57:08'),
(8, 'blink-182', 'Панк-рок / Поп-панк', 'https://i.scdn.co/image/ab6761610000e5eb5da36f8b98dd965336a1507a', '2026-08-16 05:57:08'),
(9, 'Panic! At The Disco', 'Эмо-рок', 'https://americansongwriter.com/wp-content/uploads/sites/7/2009/07/panic-at-the-disco.jpg?w=620', '2026-08-16 05:57:08'),
(10, 'Twenty One Pilots', 'Альтернативный рок', 'https://c.files.bbci.co.uk/142B7/production/_92351628_twentyonepilots-mainpub-jabarijacobs.jpg', '2026-08-16 05:57:08'),
(11, 'Limp Bizkit', 'Ню-метал', 'https://www.billboard.com/wp-content/uploads/media/Limp-Bizkit-1997-billboard-1548.jpg?w=942&h=628&crop=1', '2026-08-16 05:57:08'),
(12, 'Evanescence', 'Ню-метал / Готик-рок', 'https://craftrecordings.com/cdn/shop/collections/evanescence_fallen_shoot__FV_5049-copyright_frank_veronsky-copy.jpg?v=1711651393', '2026-08-16 05:57:08'),
(13, 'Pierce The Veil', 'Ню-метал / Эмо-рок', 'https://concord.com/wp-content/uploads/2016/03/PTV.webp', '2026-08-16 05:57:08'),
(14, 'Avril Lavigne', 'Панк-рок / Поп-рок', 'https://i.namu.wiki/i/nJniFgt0z1c9YQXUygrMyUWnNov2TBF11U_fwcL9Q54ydf4dFs-oi-YTorer6r1qtNNDyBjWeMeIH108Z5D8pQ.webp', '2026-08-16 05:57:08'),
(15, 'Paramore', 'Эмо-рок', 'https://miro.medium.com/1*FJtXHznt2WK-AcBbgVtF-A.jpeg', '2026-08-16 05:57:08');
```

### Шаг 5. Проверка работы сайта
После успешного выполнения SQL-скрипта откройте браузер и перейдите по ссылке:
[http://localhost/medialibrary/](http://localhost/medialibrary/)

### Шаг 0 (дополнительно). Онлайн-версия
Рабочая версия проекта также размещена на бесплатном хостинге с серверной базой данных MySQL.<br>
Открыть сайт можно по ссылке:
[http://kangy.free.je](http://kangy.free.je)<br>

> **⚠️ Конфигурация подключения к серверной базе данных не хранится в репозитории. Для локального запуска используются параметры подключения, указанные выше.**

## 🎨 Дизайн
Проект создан как небольшая персональная музыкальная библиотека с упором на визуальную часть и удобную навигацию.
Основные страницы:
* 🏠 Главная
* 🎤 Исполнители
* 💿 Альбомы
* ❤️ Избранное
* 👤 Профиль
* ℹ️ Информация

## 📌 Цель проекта
Проект создан в учебных целях для практики разработки полноценного веб-приложения с использованием:
* frontend-разработки;
* PHP;
* работы с MySQL;
* авторизации пользователей;
* PHP Sessions;
* загрузки файлов;
* взаимодействия frontend и backend.

### 🔮 Возможные улучшения
В дальнейшем проект можно расширить:
* Добавить полноценный музыкальный плеер
* Добавить оценки и отзывы
* Добавить админ-панель
* Улучшить систему безопасности
* Перенести настройки БД в .env
* Оптимизировать мобильную версию
* Добавить больше исполнителей и альбомов
