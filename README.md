# Computer Store - Онлайн-магазин компьютеров(http://computer-store.liveblog365.com/)

Добро пожаловать в репозиторий проекта **Computer Store** — простого интернет-магазина для продажи компьютеров. Этот проект разработан с использованием PHP, MySQL и HTML/CSS. Ниже описаны шаги для запуска сайта на локальном сервере и ключевые моменты.

---

## Требования

Чтобы запустить сайт на локальном сервере (локалке), убедись, что у тебя установлено следующее:

- **PHP**: Версия 7.4 или выше (рекомендуется 8.0+).
- **MySQL**: Сервер базы данных (например, через XAMPP, WAMP или отдельную установку).
- **Веб-сервер**: Apache или аналогичный (входит в XAMPP/WAMP).
- **Текстовый редактор**: Например, VS Code, Notepad++ или любой другой для редактирования файлов.
- **XAMPP** (рекомендуется): Пакет для быстрого запуска (включает Apache, MySQL и PHP).

---

## Установка и запуск на локалке

Следуй этим шагам, чтобы настроить и запустить проект:

### 1. Установка XAMPP
- Скачай и установи XAMPP с [официального сайта](https://www.apachefriends.org/).
- Запусти XAMPP Control Panel.
- Включи модули **Apache** и **MySQL** (нажмите "Start" для каждого).

### 2. Копирование файлов
- Скачай или скопируй папку проекта `computer-store` из этого репозитория.
- Помести её в директорию `htdocs` XAMPP (обычно `C:\xampp\htdocs\` на Windows).
  - Пример пути: `C:\xampp\htdocs\computer-store\`.

### 3. Настройка базы данных
- Открой **phpMyAdmin** (доступен по адресу `http://localhost/phpmyadmin` после запуска XAMPP).
- Создай новую базу данных, например, `computer_store`.
- Импортируй SQL-файл (если есть) или создай таблицы вручную:
  ```sql
  CREATE TABLE users (
      id INT PRIMARY KEY AUTO_INCREMENT,
      login VARCHAR(50) NOT NULL,
      email VARCHAR(100) NOT NULL,
      password VARCHAR(255) NOT NULL
  );

  CREATE TABLE products (
      id INT PRIMARY KEY AUTO_INCREMENT,
      name VARCHAR(255) NOT NULL,
      image VARCHAR(255) NOT NULL,
      price DECIMAL(10, 2) NOT NULL
  );

  CREATE TABLE cart (
      id INT PRIMARY KEY AUTO_INCREMENT,
      user_id INT NOT NULL,
      product_id INT NOT NULL,
      price DECIMAL(10, 2) NOT NULL,
      quantity INT NOT NULL,
      FOREIGN KEY (user_id) REFERENCES users(id),
      FOREIGN KEY (product_id) REFERENCES products(id)
  );

  CREATE TABLE orders (
      id INT PRIMARY KEY AUTO_INCREMENT,
      user_id INT NOT NULL,
      fio VARCHAR(100) NOT NULL,
      email VARCHAR(100) NOT NULL,
      card_number VARCHAR(19) NOT NULL,
      total DECIMAL(10, 2) NOT NULL,
      created_at DATETIME NOT NULL,
      FOREIGN KEY (user_id) REFERENCES users(id)
  );
  ```
- Запомни имя базы данных, имя пользователя (по умолчанию `root`) и пароль (по умолчанию пустой для XAMPP).

### 4. Настройка файла подключения
- Открой файл `includes/db.php` в папке проекта.
- Обнови параметры подключения:
  ```php
  <?php
  $host = 'localhost';
  $db_name = 'computer_store';
  $username = 'root';
  $password = ''; // Оставьте пустым для XAMPP по умолчанию

  $conn = new mysqli($host, $username, $password, $db_name);

  if ($conn->connect_error) {
      die("Ошибка подключения: " . $conn->connect_error);
  }
  ?>
  ```
- Сохрани файл.
## Основные моменты

- **Функциональность**: Сайт позволяет регистрироваться, входить, добавлять товары в корзину, просматривать профиль и оформлять заказы.
- **Изображения**: Убедись, что папка `images/` содержит файлы (например, `ПК1.png`), иначе товары не отобразятся.
- **Отладка**: Для просмотра ошибок добавь в начало `index.php`:

