<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT login, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT product_name, price, quantity FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_POST['clear_cart'])) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Refresh:0");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --dark-bg-start: #0a0f2b; --dark-bg-end: #000000; --star-color: #ffffff; --text-color: #e0e0e0; --accent-color: #007bff; }
        body { margin: 0; padding: 0; font-family: 'Inter', sans-serif; color: var(--text-color); overflow-x: hidden; background: linear-gradient(135deg, var(--dark-bg-start), var(--dark-bg-end)); min-height: 100vh; position: relative; }
        .stars { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; }
        .star { position: absolute; background: var(--star-color); border-radius: 50%; animation: twinkle 5s infinite alternate; }
        @keyframes twinkle { 0% { opacity: 0.3; } 50% { opacity: 1; } 100% { opacity: 0.3; } }
        .profile-container { max-width: 600px; margin: 50px auto; padding: 30px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); color: var(--text-color); position: relative; z-index: 1; }
        .profile-container h2 { color: var(--accent-color); text-align: center; margin-bottom: 25px; font-size: 1.8rem; text-shadow: 0 0 10px rgba(0, 123, 255, 0.3); }
        .user-info, .cart-items { margin-bottom: 20px; padding: 15px; background: rgba(0, 0, 0, 0.2); border-radius: 8px; }
        .cart-items table { width: 100%; border-collapse: collapse; }
        .cart-items th, .cart-items td { padding: 10px; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .cart-items th { background: rgba(0, 123, 255, 0.1); }
        .btn { padding: 10px 20px; background-color: var(--accent-color); color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; transition: all 0.3s; }
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
            <a href="profile.php">Личный кабинет</a>
            <a href="logout.php">Выход</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
    </nav>

    <div class="profile-container">
        <h2>Личный кабинет</h2>
        <div class="user-info">
            <p><strong>Логин:</strong> <?php echo htmlspecialchars($user['login']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <div class="cart-items">
            <h3>Ваша корзина</h3>
            <?php if (empty($cart_items)): ?>
                <p>Корзина пуста.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Товар</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Итого</th>
                    </tr>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> ₽</td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> ₽</td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <form method="post" style="margin-top: 15px;">
                    <input type="submit" value="Очистить корзину" class="btn" name="clear_cart">
                </form>
            <?php endif; ?>
        </div>
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