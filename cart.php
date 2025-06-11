<?php
session_start();
include 'includes/db.php'; // Подключение к базе данных

// Логика добавления, удаления и оформления заказа
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add']) && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
    header("Location: cart.php");
    exit();
}

if (isset($_POST['remove']) && isset($_POST['id'])) {
    $product_id = (int)$_POST['id'];
    if (isset($_SESSION['cart'][$product_id])) unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit();
}

if (isset($_POST['place_order'])) {
    $fio = htmlspecialchars(trim($_POST['fio']));
    $email = htmlspecialchars(trim($_POST['email']));
    $card_number = htmlspecialchars(trim($_POST['card_number']));
    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    $ids = array_keys($cart);
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare("SELECT price FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $total += $row['price'] * ($cart[$row['id']] ?? 0);
        }
    }
    $stmt = $conn->prepare("INSERT INTO orders (fio, email, card_number, total) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssd", $fio, $email, $card_number, $total);
        if ($stmt->execute()) {
            unset($_SESSION['cart']);
            header("Location: cart.php?order=success");
        } else echo "Ошибка при сохранении заказа: " . $conn->error;
        $stmt->close();
    } else echo "Ошибка подготовки запроса: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/global.css">
    <style>
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
            color: #e0e0e0;
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
            background-color: #007bff;
        }

        .btn:hover {
            background-color: #0069d9;
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .total {
            font-weight: bold;
            margin-top: 20px;
            color: #e0e0e0;
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
            color: #e0e0e0;
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
            color: #e0e0e0;
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
            color: #e0e0e0;
            border-radius: 8px;
        }
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

    <div class="container">
        <h1 style="text-align: center; margin-bottom: 30px;">Ваша корзина</h1>
        <?php
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)): ?>
            <p style="text-align: center; color: #e0e0e0;">Ваша корзина пуста</p>
            <a href="index.php" class="btn">Вернуться к покупкам</a>
        <?php else:
            $total = 0;
            $ids = array_keys($cart);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
            $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
            $stmt->execute();
            $result = $stmt->get_result();
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
                <?php while ($row = $result->fetch_assoc()):
                    $subtotal = $row['price'] * $cart[$row['id']];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= number_format($row['price'], 2, ',', ' ') ?> ₽</td>
                        <td><?= $cart[$row['id']] ?></td>
                        <td><?= number_format($subtotal, 2, ',', ' ') ?> ₽</td>
                        <td>
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="remove" class="remove-btn">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
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

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) modal.style.display = 'none';
        }
    </script>
</body>
</html>