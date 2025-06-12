<?php
session_start();
include 'includes/db.php';

ini_set('display_errors', 1); // Включение отладки
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $password = $_POST['password'];

    // Отладка: вывести введённые данные
    echo "Попытка входа: Login = '$login', Password = '$password'<br>";

    $stmt = $conn->prepare("SELECT id, password, is_admin FROM users WHERE login = ?");
    if ($stmt === false) {
        die("Ошибка подготовки запроса: " . $conn->error);
    }
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Отладка: вывести данные из базы
        echo "Найден пользователь: <pre>"; var_dump($user); echo "</pre>";

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login'] = $login;
            $_SESSION['is_admin'] = (bool)$user['is_admin']; // Устанавливаем роль

            echo "Авторизация успешна. Роль: " . ($_SESSION['is_admin'] ? 'Администратор' : 'Пользователь') . "<br>";
            if ($_SESSION['is_admin']) {
                header("Location: admin.php");
            } else {
                header("Location: profile.php");
            }
            exit();
        } else {
            $error = "<p style='color: #dc3545; text-align: center;'>Неверный пароль. Хеш в базе: " . $user['password'] . "</p>";
        }
    } else {
        $error = "<p style='color: #dc3545; text-align: center;'>Пользователь с логином '$login' не найден.</p>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg-start: #0a0f2b;
            --dark-bg-end: #000000;
            --star-color: #ffffff;
            --text-color: #e0e0e0;
            --accent-color: #007bff;
            --card-bg: rgba(10, 15, 43, 0.7);
            --card-border: rgba(0, 123, 255, 0.2);
        }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; color: var(--text-color); overflow-x: hidden; background: linear-gradient(135deg, var(--dark-bg-start), var(--dark-bg-end)); min-height: 100vh; position: relative; }
        body::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('images/space-texture.jpg') no-repeat center center/cover; opacity: 0.3; z-index: -1; }
        .stars { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        .star { position: absolute; background: var(--star-color); border-radius: 50%; animation: twinkle 5s infinite alternate; }
        @keyframes twinkle { 0% { opacity: 0.3; } 50% { opacity: 1; } 100% { opacity: 0.3; } }
        .login-container { max-width: 400px; margin: 50px auto; padding: 30px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); color: var(--text-color); position: relative; z-index: 1; }
        .login-container h2 { color: var(--accent-color); text-align: center; margin-bottom: 25px; font-size: 1.8rem; text-shadow: 0 0 10px rgba(0, 123, 255, 0.3); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; box-sizing: border-box; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; color: var(--text-color); font-size: 1rem; transition: all 0.3s; }
        .form-group input:focus { border-color: var(--accent-color); outline: none; box-shadow: 0 0 10px rgba(0, 123, 255, 0.3); }
        .btn { width: 100%; padding: 12px; background-color: var(--accent-color); color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.3s; font-size: 1.1rem; }
        .btn:hover { background-color: #0069d9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4); }
        nav { background-color: rgba(10, 15, 43, 0.9); padding: 15px 30px; text-align: center; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(5px); box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3); }
        nav a { color: var(--text-color); margin: 0 15px; text-decoration: none; font-weight: 500; font-size: 1.1rem; transition: all 0.3s; padding: 8px 15px; border-radius: 20px; }
        nav a:hover { color: var(--accent-color); text-shadow: 0 0 10px rgba(0, 123, 255, 0.5); background: rgba(0, 123, 255, 0.1); }
    </style>
</head>
<body>
    <div class="stars"></div>
    <nav>
        <a href="index.php">Главная</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="cart.php">Корзина</a>
        <?php endif; ?>
        <a href="login.php">Вход</a>
        <a href="register.php">Регистрация</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php endif; ?>
    </nav>

    <div class="login-container">
        <h2>Вход</h2>
        <form method="post">
            <div class="form-group"><label for="login">Логин</label><input type="text" id="login" name="login" required></div>
            <div class="form-group"><label for="password">Пароль</label><input type="password" id="password" name="password" required></div>
            <input type="submit" value="Войти" class="btn" name="login_submit">
        </form>
        <?php if (isset($error)) echo $error; ?>
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