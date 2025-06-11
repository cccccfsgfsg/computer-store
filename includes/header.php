<?php if (isset($_SESSION['user'])): ?>
    <p>Привет, <?= $_SESSION['user']['name'] ?> | <a href="logout.php">Выход</a></p>
<?php else: ?>
    <a href="login.php">Вход</a> | <a href="register.php">Регистрация</a>
<?php endif; ?>