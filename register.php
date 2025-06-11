<?php
session_start();
include 'includes/db.php'; // Подключение к базе данных

// Обработка регистрации (примерная логика, адаптируйте под вашу базу данных)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_submit'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хеширование пароля
    $email = htmlspecialchars(trim($_POST['email']));

    // Проверка уникальности логина и email (пример)
    $stmt = $conn->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
    $stmt->bind_param("ss", $login, $email);
    $stmt->execute();
    if ($stmt->fetch()) {
        echo "<p style='color: #dc3545; text-align: center;'>Логин или email уже заняты.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (login, password, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $login, $password, $email);
        if ($stmt->execute()) {
            header("Location: login.php?registered=true");
            exit();
        } else {
            echo "<p style='color: #dc3545; text-align: center;'>Ошибка регистрации: " . $conn->error . "</p>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: var(--text-color);
        }
        .register-container h2 { color: var(--accent-color); text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; box-sizing: border-box; background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: var(--text-color); }
        .btn { width: 100%; padding: 12px; background-color: var(--accent-color); color: white; border: none; border-radius: 5px; cursor: pointer; transition: all 0.3s; }
        .btn:hover { background-color: #0069d9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3); }
    </style>
</head>
<body>
    <div class="stars"></div>
    <nav>
        <a href="index.php">Главная</a>
        <a href="cart.php">Корзина</a>
        <a href="login.php">Вход</a>
        <a href="register.php">Регистрация</a>
    </nav>

    <div class="register-container">
        <h2>Регистрация</h2>
        <form method="post">
            <div class="form-group"><label for="login">Логин</label><input type="text" id="login" name="login" required></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
            <div class="form-group"><label for="password">Пароль</label><input type="password" id="password" name="password" required></div>
            <div class="form-group"><label for="confirm_password">Подтвердите пароль</label><input type="password" id="confirm_password" name="confirm_password" required></div>
            <input type="submit" value="Зарегистрироваться" class="btn" name="register_submit">
        </form>
        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'true'): ?>
            <p style="color: #28a745; text-align: center; margin-top: 15px;">Регистрация успешна! Теперь вы можете войти.</p>
        <?php endif; ?>
    </div>

    <script>
        function createStars() {
            const starsContainer = document.querySelector('.stars');
            for (let i = 0; i < 100; i++) {
                const star = document.createElement('div');
                const size = Math.random() * 2 + 1;
                star.className = 'star';
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.left = `${Math.random() * 100}vw`;
                star.style.top = `${Math.random() * 100}vh`;
                star.style.animationDelay = `${Math.random() * 5}s`;
                starsContainer.appendChild(star);
            }
        }
        createStars();
    </script>
</body>
</html>