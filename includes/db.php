<?php
$host = 'localhost'; // Хост базы данных
$dbname = 'computer_store'; // Имя вашей базы данных
$username = 'root'; // Имя пользователя базы данных
$password = ''; // Пароль (оставьте пустым, если нет пароля)

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Ошибка подключения: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Ошибка: " . $e->getMessage());
}
?>