<?php
session_start();
include 'includes/db.php';
$user_id = $_SESSION['user']['id'] ?? 0;

if (!$user_id) {
  die("Только для авторизованных пользователей!");
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) die("Корзина пуста!");

$conn->query("INSERT INTO orders (user_id) VALUES ($user_id)");
$order_id = $conn->insert_id;

foreach ($cart as $id => $qty) {
  $p = $conn->query("SELECT price FROM products WHERE id=$id")->fetch_assoc();
  $price = $p['price'];
  $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $id, $qty, $price)");
}
unset($_SESSION['cart']);
echo "Заказ оформлен!";
?>
