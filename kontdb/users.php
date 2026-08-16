<?php

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'kont_db';


$connection = new mysqli($host, $username, $password, $database);

if ($connection->connect_error) {
    die("Ошибка подключения: " . $connection->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];


    $sql = "INSERT INTO messages (name, email, message) VALUES ('$name', '$email', '$message')";


    if ($connection->query($sql) === TRUE) {
        echo "<script>alert('Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.');</script>";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $connection->error;
    }
}

$connection->close();
?>