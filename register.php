<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <style>
        /* Адаптивный дизайн */
        body { font-family: Arial; max-width: 390px; margin: 0 auto; padding: 20px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h2>Регистрация</h2>
    <?php
    include 'config.php';
    $errors = [];
 
 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Валидация данных
        $login = $_POST['login'];
        $password = $_POST['password'];
        $full_name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
 
 
        // Проверка уникальности логина
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors[] = "Логин уже занят.";
        }
 
 




        // Регулярные выражения для валидации
        if (!preg_match('/^[А-Яа-яёЁ\s]+$/u', $full_name)) $errors[] = "ФИО должно содержать только кириллицу.";
        if (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $phone)) $errors[] = "Неверный формат телефона.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Неверный формат email.";
        if (strlen($password) < 6) $errors[] = "Пароль должен быть не менее 6 символов.";
 
 
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (login, password, full_name, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$login, $hashed_password, $full_name, $phone, $email]);
            header("Location: index.php");
            exit();
        }
    }
    ?>
 
 
    <form method="post">
        <?php foreach ($errors as $error): ?>
            <div class="error"><?= $error ?></div>
        <?php endforeach; ?>
 
 
        <input type="text" name="login" placeholder="Логин" required><br>
        <input type="password" name="password" placeholder="Пароль" required><br>
        <input type="text" name="full_name" placeholder="ФИО" required><br>
        <input type="tel" name="phone" placeholder="+7(XXX)-XXX-XX-XX" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <button type="submit">Зарегистрироваться</button>
    </form>
    <a href="../index.php">Авторизироваться</a>
</body>
</html>
