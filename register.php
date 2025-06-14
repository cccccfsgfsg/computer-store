<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_submit'])) {
    $login = htmlspecialchars(trim($_POST['login']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "<p style='color: #dc3545; text-align: center;'>Пароли не совпадают.</p>";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE login = ? OR email = ?");
        $stmt->bind_param("ss", $login, $email);
        $stmt->execute();
        if ($stmt->fetch()) {
            $error = "<p style='color: #dc3545; text-align: center;'>Логин или email уже заняты.</p>";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (login, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $login, $hashed_password, $email);

            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['login'] = $login;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $product_id => $quantity) {
                        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_name, price, quantity) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
                        $product_name = "ПК " . $product_id;
                        $price = [50000, 60000, 70000][$product_id - 1] ?? 0;
                        $stmt->bind_param("issdi", $user_id, $product_name, $price, $quantity, $quantity);
                        $stmt->execute();
                    }
                    unset($_SESSION['cart']);
                }
                header("Location: profile.php");
                exit();
            } else {
                $error = "<p style='color: #dc3545; text-align: center;'>Ошибка регистрации: " . $conn->error . "</p>";
            }
        }
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
    <title>Регистрация</title>
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
        .register-container { max-width: 400px; margin: 50px auto; padding: 30px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); color: var(--text-color); position: relative; z-index: 1; }
        .register-container h2 { color: var(--accent-color); text-align: center; margin-bottom: 25px; font-size: 1.8rem; text-shadow: 0 0 10px rgba(0, 123, 255, 0.3); }
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

    <div class="register-container">
        <h2>Регистрация</h2>
        <form method="post">
            <div class="form-group"><label for="login">Логин</label><input type="text" id="login" name="login" required></div>
            <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
            <div class="form-group"><label for="password">Пароль</label><input type="password" id="password" name="password" required></div>
            <div class="form-group"><label for="confirm_password">Подтвердите пароль</label><input type="password" id="confirm_password" name="confirm_password" required></div>
            <input type="submit" value="Зарегистрироваться" class="btn" name="register_submit">
        </form>
        <?php if (isset($error)): ?>
            <?php echo $error; ?>
        <?php endif; ?>
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