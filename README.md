# MyLittleMediaLibrary_web
My Little Media Library — это веб-приложение для хранения и просмотра музыкальной медиатеки.
Проект позволяет пользователям просматривать исполнителей и альбомы, добавлять понравившийся контент в избранное и работать с личным аккаунтом.

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
```

### Шаг 5. Проверка работы сайта
После успешного выполнения SQL-скрипта откройте браузер и перейдите по ссылке:
[http://localhost/medialibrary/](http://localhost/medialibrary/)

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
