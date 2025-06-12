<?php
session_start();
include 'includes/db.php';

// Обработка добавления в корзину
if (isset($_POST['add']) && isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = 1;

    // Проверка существования продукта
    $stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if (!$product) {
        die("Ошибка: Продукт с ID $product_id не найден в таблице products.");
    }

    $product_name = $product['name'];
    $price = $product['price'];

    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();

    if ($cart_item) {
        $new_quantity = $cart_item['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_quantity, $cart_item['id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisdi", $_SESSION['user_id'], $product_id, $product_name, $price, $quantity);
    }
    if (!$stmt->execute()) {
        die("Ошибка при добавлении в корзину: " . $conn->error);
    }
    header("Location: index.php");
    exit();
}

// Загрузка всех продуктов
$stmt = $conn->prepare("SELECT id, name, image, price FROM products");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
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
        .product { display: flex; flex-direction: column; align-items: center; margin: 15px; text-align: center; border: 1px solid var(--card-border); padding: 25px; border-radius: 25px; transition: all 0.3s ease; background: var(--card-bg); backdrop-filter: blur(8px); width: 250px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); overflow: hidden; position: relative; }
        .product::before { content: ""; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(0,123,255,0.1) 0%, transparent 70%); transform: rotate(45deg); z-index: -1; opacity: 0; transition: opacity 0.5s ease; }
        .product:hover { transform: translateY(-8px); box-shadow: 0 8px 30px rgba(0, 123, 255, 0.3); border-color: rgba(0, 123, 255, 0.3); }
        .product:hover::before { opacity: 1; }
        .product img { max-width: 100%; height: 160px; object-fit: contain; margin-bottom: 15px; filter: drop-shadow(0 0 8px rgba(0, 123, 255, 0.3)); background: rgba(255,255,255,0.05); padding: 10px; border-radius: 15px; }
        .product h3 { margin: 10px 0; font-size: 1.2rem; color: var(--text-color); text-shadow: 0 0 5px rgba(0, 123, 255, 0.2); }
        .product-price { font-size: 1.3rem; font-weight: 600; color: var(--accent-color); margin: 10px 0; text-shadow: 0 0 8px rgba(0, 123, 255, 0.3); }
        .add-to-cart { background-color: var(--accent-color); color: white; border: none; padding: 12px 25px; border-radius: 25px; font-weight: 500; cursor: pointer; transition: all 0.3s; width: 100%; margin-top: 15px; letter-spacing: 0.5px; }
        .add-to-cart:hover { background-color: #0069d9; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3); }
        .add-to-cart:disabled { background-color: #6c757d; cursor: not-allowed; }
        nav { background-color: rgba(10, 15, 43, 0.9); padding: 15px 30px; text-align: center; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(5px); box-shadow: 0 2px 15px rgba(0, 0, 0, 0.3); }
        nav a { color: var(--text-color); margin: 0 15px; text-decoration: none; font-weight: 500; font-size: 1.1rem; transition: all 0.3s; padding: 8px 15px; border-radius: 20px; }
        nav a:hover { color: var(--accent-color); text-shadow: 0 0 10px rgba(0, 123, 255, 0.5); background: rgba(0, 123, 255, 0.1); }
        .container { max-width: 1200px; margin: 40px auto; padding: 20px; position: relative; z-index: 1; text-align: center; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; justify-items: center; }
        h1 { font-size: 2.5rem; margin-bottom: 40px; text-shadow: 0 0 15px rgba(0, 123, 255, 0.3); position: relative; display: inline-block; grid-column: 1 / -1; }
        h1::after { content: ""; position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%); width: 100px; height: 3px; background: var(--accent-color); border-radius: 3px; box-shadow: 0 0 10px var(--accent-color); }
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

    <div class="container">
        <h1>Каталог товаров</h1>
        <?php foreach ($products as $product): ?>
            <div class="product">
                <?php
                $imagePath = "images/" . htmlspecialchars($product['image']);
                if (file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '" alt="' . htmlspecialchars($product['name']) . '">';
                } else {
                    echo '<div class="no-image">Изображение отсутствует</div>';
                }
                ?>
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <div class="product-price"><?php echo number_format($product['price'], 2, ',', ' '); ?> ₽</div>
                <form method="post" action="index.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" name="add" class="add-to-cart" <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>В корзину</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function createStars() {
            const starsContainer = document.querySelector('.stars');
            for (let i = 0; i < 150; i++) {
                const star = document.createElement('div');
                const size = Math.random() * 3;
                star.className = 'star';
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                star.style.left = `${Math.random() * 100}vw`;
                star.style.top = `${Math.random() * 100}vh`;
                star.style.animationDelay = `${Math.random() * 5}s`;
                star.style.opacity = Math.random();
                starsContainer.appendChild(star);
            }
        }
        createStars();
    </script>
</body>
</html>