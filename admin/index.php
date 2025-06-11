<?php
session_start();
include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav>
        <a href="index.php">Главная</a>
        <a href="cart.php">Корзина</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="logout.php">Выход (<?= htmlspecialchars($_SESSION['user']['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Вход</a>
            <a href="register.php">Регистрация</a>
        <?php endif; ?>
    </nav>

    <div class="container">
        <h1>Каталог товаров</h1>
        <?php
        $result = $conn->query("SELECT * FROM products");
        if ($result && $result->num_rows > 0):
            while ($row = $result->fetch_assoc()):
        ?>
            <div class="product">
                <img src="images/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <h3><?= htmlspecialchars($row['name']) ?></h3>
                <p><?= number_format($row['price'], 2, ',', ' ') ?> ₽</p>
                <form method="post" action="cart.php">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <input type="submit" name="add" value="Добавить в корзину">
                </form>
            </div>
        <?php endwhile; else: ?>
            <p>Товары пока не добавлены.</p>
        <?php endif; ?>
    </div>
</body>
</html>