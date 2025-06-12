<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'computer_store');
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Отладка: вывести текущую сессию
// echo "<pre>"; var_dump($_SESSION); echo "</pre>"; exit;

// Проверка прав доступа (модератор)
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php?error=access_denied");
    exit();
}

// Обработка добавления нового товара
if (isset($_POST['add_product'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $image = $_FILES['image']['name'];
    $price = floatval($_POST['price']);
    if ($image && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/";
        $target_file = $target_dir . basename($image);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $conn->query("INSERT INTO products (name, image, price) VALUES ('$name', '$image', '$price')");
            $message = "Товар добавлен успешно!";
        } else {
            $message = "Ошибка при перемещении изображения.";
        }
    } else {
        $message = "Ошибка загрузки изображения.";
    }
}

// Обработка обновления статуса заказа
if (isset($_POST['update_order'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    if ($conn->query("UPDATE orders SET status = '$status' WHERE id = $order_id")) {
        $message = "Статус заказа обновлен!";
    } else {
        $message = "Ошибка обновления статуса.";
    }
}

// Получение списка заказов для отображения
$orders_result = $conn->query("SELECT * FROM orders");
if (!$orders_result) {
    die("Ошибка запроса заказов: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель модератора - Computer Store</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; }
        .form-group input, .form-group select { padding: 5px; width: 200px; }
        .form-group button { padding: 5px 10px; }
        .orders-table { border-collapse: collapse; margin-top: 20px; }
        .orders-table td, .orders-table th { border: 1px solid #ddd; padding: 8px; }
        .message { color: green; margin-top: 10px; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <h2>Панель модератора</h2>

    <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'access_denied') echo "<p class='error'>Вы не авторизованы как модератор. Пожалуйста, войдите.</p>"; ?>

    <!-- Форма добавления товара -->
    <h3>Добавить новый товар</h3>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">Название товара:</label>
            <input type="text" name="name" id="name" required>
        </div>
        <div class="form-group">
            <label for="image">Изображение:</label>
            <input type="file" name="image" id="image" required>
        </div>
        <div class="form-group">
            <label for="price">Цена (руб.):</label>
            <input type="number" name="price" id="price" step="0.01" required>
        </div>
        <div class="form-group">
            <button type="submit" name="add_product">Добавить товар</button>
        </div>
    </form>

    <!-- Таблица заказов для обработки -->
    <h3>Список заказов</h3>
    <table class="orders-table">
        <tr>
            <th>ID заказа</th>
            <th>Пользователь</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Действие</th>
        </tr>
        <?php while ($order = $orders_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $order['id']; ?></td>
                <td><?php echo $order['fio']; ?></td>
                <td><?php echo $order['total']; ?> руб.</td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="Обрабатывается" <?php echo $order['status'] == 'Обрабатывается' ? 'selected' : ''; ?>>Обрабатывается</option>
                            <option value="Выполнен" <?php echo $order['status'] == 'Выполнен' ? 'selected' : ''; ?>>Выполнен</option>
                        </select>
                    </form>
                </td>
                <td><a href="?delete_order=<?php echo $order['id']; ?>" onclick="return confirm('Удалить заказ?');">Удалить</a></td>
            </tr>
        <?php } ?>
    </table>

    <?php
    // Обработка удаления заказа
    if (isset($_GET['delete_order'])) {
        $order_id = intval($_GET['delete_order']);
        if ($conn->query("DELETE FROM orders WHERE id = $order_id")) {
            header("Location: admin.php");
        } else {
            $message = "Ошибка удаления заказа.";
        }
    }
    ?>

    <p><a href="index.php">Вернуться на главную</a></p>
</body>
</html>

<?php
$conn->close();
?>