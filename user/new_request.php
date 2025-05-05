<?php
session_start();
include '../config.php';
 
 
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
 
 
$user_id = $_SESSION['user_id'];
$errors = [];
 
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $service_type = $_POST['service_type'];
    $custom_service = $_POST['custom_service'] ?? '';
    $payment_type = $_POST['payment_type'];
    $datetime = $_POST['datetime'];
 
 
    // Валидация
    if (empty($address)) $errors[] = "Адрес обязателен";
    if (!preg_match('/^\+7\(\d{3}\)-\d{3}-\d{2}-\d{2}$/', $phone)) $errors[] = "Неверный формат телефона";
    if ($service_type === 'other' && empty($custom_service)) $errors[] = "Опишите услугу";
    if (strtotime($datetime) < time()) $errors[] = "Дата должна быть в будущем";
 
 
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO requests 
            (user_id, address, phone, service_type, custom_service, payment_type, desired_datetime)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $address, $phone, $service_type, $custom_service, $payment_type, $datetime]);
        header("Location: ./user/create_request.php?success=1");
        exit();
    }
}
 
 
// Получение данных пользователя для подстановки
$stmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка</title>
    <style>
        .hidden { display: none; }
        .error { color: red; }
    </style>
    <script>
        function toggleCustomService(select) {
            document.getElementById('custom-service').style.display = 
                select.value === 'other' ? 'block' : 'none';
        }
    </script>
</head>
<body>

    <header>
        <a href="../session_destroy.php">Выйти</a>
    </header>

    <h2>Новая заявка</h2>
    <?php foreach ($errors as $error): ?>
        <div class="error"><?= $error ?></div>
    <?php endforeach; ?>
    
    <form method="post">
        <input type="text" name="address" placeholder="Адрес" required><br>
        <input type="tel" name="phone" placeholder="Телефон" 
               value="<?= htmlspecialchars($user['phone']) ?>" required><br>
        
        <select name="service_type" onchange="toggleCustomService(this)" required>
            <option value="">Выберите услугу</option>
            <option value="general">Общий клининг</option>
            <option value="deep">Генеральная уборка</option>
            <option value="post_construction">Послестроительная уборка</option>
            <option value="carpet">Химчистка ковров и мебели</option>
            <option value="other">Иная услуга</option>
        </select><br>
        
        <div id="custom-service" class="hidden">
            <textarea name="custom_service" placeholder="Опишите услугу"></textarea><br>
        </div>
        
        <input type="datetime-local" name="datetime" required><br>
        
        <select name="payment_type" required>
            <option value="">Тип оплаты</option>
            <option value="cash">Наличные</option>
            <option value="card">Банковская карта</option>
        </select><br>
        
        <button type="submit">Создать заявку</button>
    </form>
</body>
</html>
