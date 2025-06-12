<?php
session_start();
// Подключение к базе данных откладываем до использования
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Проверка авторизации
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Логика добавления, удаления и оформления заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php'; // Подключаем базу данных только при необходимости

    if (isset($_POST['add']) && isset($_POST['product_id'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);

        // Проверка наличия товара в корзине
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();

        if ($cart_item) {
            $new_quantity = $cart_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
        } else {
            $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            if ($product) {
                $price = $product['price'];
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, price, quantity) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iidi", $user_id, $product_id, $price, $quantity);
            }
        }
        $stmt->execute();
        header("Location: cart.php");
        exit();
    }

    if (isset($_POST['remove']) && isset($_POST['id'])) {
        $cart_id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        header("Location: cart.php");
        exit();
    }

    if (isset($_POST['place_order'])) {
        $fio = htmlspecialchars(trim($_POST['fio']));
        $email = htmlspecialchars(trim($_POST['email']));
        $card_number = htmlspecialchars(trim($_POST['card_number']));

        // Подсчёт общей суммы
        $stmt = $conn->prepare("SELECT product_id, price, quantity FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $total = 0;
        foreach ($cart_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        // Сохранение заказа
        $stmt = $conn->prepare("INSERT INTO orders (user_id, fio, email, card_number, total, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("issdd", $user_id, $fio, $email, $card_number, $total);
            if ($stmt->execute()) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                header("Location: cart.php?order=success");
            } else {
                $error = "Ошибка при сохранении заказа: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error = "Ошибка подготовки запроса: " . $conn->error;
        }
        exit();
    }
}

// Подключаем базу данных для вывода данных
include 'includes/db.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
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
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text-color);
            overflow-x: hidden;
            background: linear-gradient(135deg, var(--dark-bg-start), var(--dark-bg-end));
            min-height: 100vh;
            position: relative;
        }
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/space-texture.jpg') no-repeat center center/cover;
            opacity: 0.3;
            z-index: -1;
        }
        .stars {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        .star {
            position: absolute;
            background: var(--star-color);
            border-radius: 50%;
            animation: twinkle 5s infinite alternate;
        }
        @keyframes twinkle {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
        .cart-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            color: var(--text-color);
            position: relative;
            z-index: 1;
        }
        .cart-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--accent-color);
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
        }
        .cart-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 15px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        .cart-table th,
        .cart-table td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-color);
        }
        .cart-table th {
            background: rgba(10, 15, 43, 0.8);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cart-table td:first-child,
        .cart-table th:first-child {
            border-radius: 15px 0 0 0;
        }
        .cart-table td:last-child,
        .cart-table th:last-child {
            border-radius: 0 15px 0 0;
        }
        .cart-table tr:last-child td {
            border-bottom: none;
            border-radius: 0 0 15px 15px;
        }
        .cart-table img {
            max-width: 100px;
            height: auto;
            border-radius: 8px;
        }
        .remove-btn,
        .btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .remove-btn:hover,
        .btn:hover {
            background-color: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        .btn {
            background-color: var(--accent-color);
        }
        .btn:hover {
            background-color: #0069d9;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .total {
            font-weight: bold;
            margin-top: 20px;
            color: var(--text-color);
            text-align: right;
            padding-right: 15px;
        }
        .order-success {
            text-align: center;
            color: #28a745;
            padding: 20px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 10px;
            margin-top: 20px;
        }
        #orderModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            color: var(--text-color);
        }
        .close {
            position: absolute;
            top: 15px;
            right: 20px;
            cursor: pointer;
            font-size: 1.8rem;
            color: #bbb;
            transition: color 0.2s;
        }
        .close:hover {
            color: var(--text-color);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-color);
            border-radius: 8px;
        }
        nav {
            background-color: rgba(10, 15, 43, 0.9);
            padding: 15px 30px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(5px);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3);
        }
        nav a {
            color: var(--text-color);
            margin: 0 15px;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s;
            padding: 8px 15px;
            border-radius: 20px;
        }
        nav a:hover {
            color: var(--accent-color);
            text-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            background: rgba(0, 123, 255, 0.1);
        }
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

    <div class="cart-container">
        <h1>Ваша корзина</h1>
        <?php
        $stmt = $conn->prepare("SELECT c.id, p.name, p.image, p.price, c.quantity FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($cart_items)): ?>
            <p style="text-align: center;">Ваша корзина пуста</p>
            <a href="index.php" class="btn">Вернуться к покупкам</a>
        <?php else:
            $total = 0;
        ?>
            <table class="cart-table">
                <tr>
                    <th>Изображение</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Количество</th>
                    <th>Сумма</th>
                    <th>Действие</th>
                </tr>
                <?php foreach ($cart_items as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><img src="images/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= number_format($item['price'], 2, ',', ' ') ?> ₽</td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($subtotal, 2, ',', ' ') ?> ₽</td>
                        <td>
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" name="remove" class="remove-btn">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="total">Итого: <?= number_format($total, 2, ',', ' ') ?> ₽</div>
            <button onclick="document.getElementById('orderModal').style.display='flex'" class="btn">Оформить заказ</button>
            <a href="index.php" class="btn">Продолжить покупки</a>
            <?php if (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
                <div class="order-success">Заказ успешно оформлен!</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('orderModal').style.display='none'">×</span>
            <h2>Оформление заказа</h2>
            <form method="post">
                <div class="form-group"><label for="fio">ФИО</label><input type="text" id="fio" name="fio" placeholder="Иван Иванов" required></div>
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" placeholder="example@mail.com" required></div>
                <div class="form-group"><label for="card_number">Номер карты</label><input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" required pattern="\d{4} \d{4} \d{4} \d{4}"></div>
                <input type="submit" name="place_order" value="Подтвердить заказ" class="btn">
            </form>
            <?php if (isset($error)) echo "<p style='color: #dc3545; text-align: center;'>$error</p>"; ?>
        </div>
    </div>

    <?php
    // Закрываем соединение только после всех операций
    $conn->close();
    ?>

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

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) modal.style.display = 'none';
        }
    </script>
</body>
</html>