<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg-start: #0a0f2b; /* Глубокий синий */
            --dark-bg-end: #000000;   /* Чёрный */
            --star-color: #ffffff;    /* Цвет звёзд */
            --text-color: #e0e0e0;    /* Цвет текста */
            --accent-color: #007bff;  /* Акцентный цвет (синий) */
            --card-bg: rgba(10, 15, 43, 0.7); /* Прозрачный фон карточек с тёмным оттенком */
            --card-border: rgba(0, 123, 255, 0.2); /* Граница с акцентным синим */
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

        /* Космический фон */
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

        /* Анимация звёзд */
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

        /* Карточки товаров */
        .product {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 15px;
            text-align: center;
            border: 1px solid var(--card-border);
            padding: 25px;
            border-radius: 25px;
            transition: all 0.3s ease;
            background: var(--card-bg);
            backdrop-filter: blur(8px);
            width: 250px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
        }

        .product::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(0, 123, 255, 0.1) 0%, transparent 70%);
            transform: rotate(45deg);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .product:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 123, 255, 0.3);
            border-color: rgba(0, 123, 255, 0.3);
        }

        .product:hover::before {
            opacity: 1;
        }

        .product img {
            max-width: 100%;
            height: 160px;
            object-fit: contain;
            margin-bottom: 15px;
            filter: drop-shadow(0 0 8px rgba(0, 123, 255, 0.3));
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 15px; /* Овальная форма для изображений */
        }

        .product h3 {
            margin: 10px 0;
            font-size: 1.2rem;
            color: var(--text-color);
            text-shadow: 0 0 5px rgba(0, 123, 255, 0.2);
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--accent-color);
            margin: 10px 0;
            text-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
        }

        .add-to-cart {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 15px;
            letter-spacing: 0.5px;
        }

        .add-to-cart:hover {
            background-color: #0069d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        /* Навигация */
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

        /* Основной контейнер с сеткой */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            position: relative;
            z-index: 1;
            text-align: center;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            justify-items: center;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 40px;
            text-shadow: 0 0 15px rgba(0, 123, 255, 0.3);
            position: relative;
            display: inline-block;
            grid-column: 1 / -1;
        }

        h1::after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
            box-shadow: 0 0 10px var(--accent-color);
        }

        /* Проверка изображения */
        .product img[src=""] {
            display: none;
        }

        .product .no-image {
            width: 100%;
            height: 160px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: var(--text-color);
            font-size: 1rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Звёзды -->
    <div class="stars"></div>

    <nav>
        <a href="index.php">Главная</a>
        <a href="cart.php">Корзина</a>
        <a href="login.php">Вход</a>
        <a href="register.php">Регистрация</a>
    </nav>

    <div class="container">
        <h1>Каталог товаров</h1>
        <div class="product">
            <?php
            $imagePath = "images/ПК1.png";
            if (file_exists($imagePath)) {
                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="ПК 1">';
            } else {
                echo '<div class="no-image">Изображение отсутствует</div>';
            }
            ?>
            <h3>ПК 1</h3>
            <div class="product-price">50 000 ₽</div>
            <button class="add-to-cart">В корзину</button>
        </div>
        <div class="product">
            <?php
            $imagePath = "images/ПК2.png";
            if (file_exists($imagePath)) {
                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="ПК 2">';
            } else {
                echo '<div class="no-image">Изображение отсутствует</div>';
            }
            ?>
            <h3>ПК 2</h3>
            <div class="product-price">60 000 ₽</div>
            <button class="add-to-cart">В корзину</button>
        </div>
        <div class="product">
            <?php
            $imagePath = "images/ПК3.png";
            if (file_exists($imagePath)) {
                echo '<img src="' . htmlspecialchars($imagePath) . '" alt="ПК 3">';
            } else {
                echo '<div class="no-image">Изображение отсутствует</div>';
            }
            ?>
            <h3>ПК 3</h3>
            <div class="product-price">70 000 ₽</div>
            <button class="add-to-cart">В корзину</button>
        </div>
    </div>

    <script>
        // Генерация звёзд
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