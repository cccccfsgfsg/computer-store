<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM products WHERE id = $id");
$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $price = floatval($_POST['price']);
    $description = htmlspecialchars($_POST['description']);
    $quantity = intval($_POST['quantity']);
    $category = intval($_POST['category']);

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, quantity=?, category_id=? WHERE id=?");
    $stmt->bind_param("sdsiii", $name, $price, $description, $quantity, $category, $id);
    $stmt->execute();

    header('Location: index.php');
    exit();
}
?>

<h2>Редактировать товар</h2>
<form method="post">
    <input type="text" name="name" value="<?= $product['name'] ?>" required><br>
    <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required><br>
    <textarea name="description"><?= $product['description'] ?></textarea><br>
    <input type="number" name="quantity" value="<?= $product['quantity'] ?>" required><br>
    <input type="number" name="category" value="<?= $product['category_id'] ?>" required><br>
    <input type="submit" value="Сохранить изменения">
</form>