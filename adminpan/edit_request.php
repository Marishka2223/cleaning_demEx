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

// Получение данных заявки для редактирования
$request_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("
    SELECT r.*, u.full_name, u.login 
    FROM requests r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ?
");
$stmt->execute([$request_id]);
$request = $stmt->fetch();

if (!$request) {
    header("Location: admin.php");
    exit();
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем значения из POST или используем значения из базы данных, если они не переданы
    $address = $_POST['address'] ?? $request['address'];
    $phone = $_POST['phone'] ?? $request['phone'];
    $service_type = $_POST['service_type'] ?? $request['service_type'];
    $custom_service = $_POST['custom_service'] ?? $request['custom_service'];
    $payment_type = $_POST['payment_type'] ?? $request['payment_type'];
    $desired_datetime = $_POST['desired_datetime'] ?? $request['desired_datetime'];
    $status = $_POST['status'] ?? $request['status'];
    $admin_comment = $_POST['admin_comment'] ?? $request['admin_comment'];

    $stmt = $pdo->prepare("
        UPDATE requests SET 
        address = ?, 
        phone = ?, 
        service_type = ?, 
        custom_service = ?, 
        payment_type = ?, 
        desired_datetime = ?, 
        status = ?, 
        admin_comment = ? 
        WHERE request_id = ?
    ");
    $stmt->execute([
        $address, 
        $phone, 
        $service_type, 
        $custom_service, 
        $payment_type, 
        $desired_datetime, 
        $status, 
        $admin_comment, 
        $request_id
    ]);

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование заявки</title>
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceTypeSelect = document.querySelector('select[name="service_type"]');
            const customServiceGroup = document.getElementById('custom-service-group');
            
            // Функция для проверки и отображения поля
            function toggleCustomServiceField() {
                if (serviceTypeSelect.value === 'other') {
                    customServiceGroup.style.display = 'block';
                } else {
                    customServiceGroup.style.display = 'none';
                }
            }
            
            // Инициализация при загрузке
            toggleCustomServiceField();
            
            // Слушатель изменения выбора
            serviceTypeSelect.addEventListener('change', toggleCustomServiceField);
        });
    </script>
    <style>
        #custom-service-group {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Редактирование заявки #<?= htmlspecialchars($request['request_id']) ?></h1>
        <a href="admin.php" class="back">Назад</a>
        
        <form method="post">
            <!-- ... (остальные поля формы остаются без изменений) ... -->
            
            <div class="form-group">
                <label>Тип услуги:</label>
                <select name="service_type" required>
                    <option value="general" <?= $request['service_type'] == 'general' ? 'selected' : '' ?>>Генеральная уборка</option>
                    <option value="deep" <?= $request['service_type'] == 'deep' ? 'selected' : '' ?>>Глубокая уборка</option>
                    <option value="post_construction" <?= $request['service_type'] == 'post_construction' ? 'selected' : '' ?>>Послестроительная уборка</option>
                    <option value="carpet" <?= $request['service_type'] == 'carpet' ? 'selected' : '' ?>>Химчистка ковров</option>
                    <option value="other" <?= $request['service_type'] == 'other' ? 'selected' : '' ?>>Другое</option>
                </select>
            </div>
            
            <div class="form-group" id="custom-service-group">
                <label>Описание другой услуги:</label>
                <input type="text" name="custom_service" value="<?= htmlspecialchars($request['custom_service'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>Способ оплаты:</label>
                <select name="payment_type" required>
                    <option value="cash" <?= $request['payment_type'] == 'cash' ? 'selected' : '' ?>>Наличные</option>
                    <option value="card" <?= $request['payment_type'] == 'card' ? 'selected' : '' ?>>Карта</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Желаемые дата и время:</label>
                <input type="datetime-local" name="desired_datetime" value="<?= date('Y-m-d\TH:i', strtotime($request['desired_datetime'])) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Статус:</label>
                <select name="status" required>
                    <option value="new" <?= $request['status'] == 'new' ? 'selected' : '' ?>>Новая</option>
                    <option value="in_progress" <?= $request['status'] == 'in_progress' ? 'selected' : '' ?>>В процессе</option>
                    <option value="completed" <?= $request['status'] == 'completed' ? 'selected' : '' ?>>Завершена</option>
                    <option value="canceled" <?= $request['status'] == 'canceled' ? 'selected' : '' ?>>Отменена</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Комментарий администратора:</label>
                <textarea name="admin_comment"><?= htmlspecialchars($request['admin_comment'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="save">Сохранить</button>
        </form>
    </div>
</body>
</html>