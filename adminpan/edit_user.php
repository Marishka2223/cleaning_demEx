<?php
session_start();
include '../config.php';

// Проверка авторизации администратора
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Проверка, что пользователь - admin
$stmt = $pdo->prepare("SELECT login FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['login'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Проверка наличия ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = (int)$_GET['id'];

// Получение данных пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: admin.php?error=user_not_found");
    exit();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        if (!empty($password)) {
            // Если указан новый пароль - обновляем все поля включая пароль
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users SET 
                full_name = ?, 
                phone = ?, 
                email = ?, 
                password = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$full_name, $phone, $email, $hashed_password, $user_id]);
        } else {
            // Если пароль не указан - обновляем только остальные поля
            $stmt = $pdo->prepare("
                UPDATE users SET 
                full_name = ?, 
                phone = ?, 
                email = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$full_name, $phone, $email, $user_id]);
        }
        
        header("Location: admin.php?success=user_updated");
        exit();
    } catch (PDOException $e) {
        // В случае ошибки БД можно вывести сообщение или записать в лог
        die("Ошибка обновления пользователя: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Редактирование пользователя</h1>
        <a href="admin.php" class="back">Назад</a>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                switch ($_GET['error']) {
                    case 'db_error': echo 'Ошибка базы данных'; break;
                    default: echo 'Произошла ошибка';
                }
                ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Логин:</label>
                <input type="text" value="<?= htmlspecialchars($user['login']) ?>" disabled>
            </div>
            
            <div class="form-group">
                <label>ФИО:</label>
                <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Телефон:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Новый пароль (оставьте пустым, чтобы не менять):</label>
                <input type="password" name="password" placeholder="Введите новый пароль">
            </div>
            
            <button type="submit" class="save">Сохранить</button>
        </form>
    </div>
</body>
</html>