<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Получение заявок с сортировкой по дате
$stmt = $pdo->prepare("SELECT * FROM requests 
                      WHERE user_id = ? 
                      ORDER BY desired_datetime DESC");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <style>
        .request-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
        }
        .status {
            padding: 5px;
            border-radius: 4px;
            color: white;
            display: inline-block;
            margin-right: 10px;
        }
        .new { background: #4CAF50; }
        .in_progress { background: #2196F3; }
        .completed { background: #9E9E9E; }
        .canceled { background: #f44336; }
        .comment {
            margin-top: 5px;
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>

    <header>
        <a href="../session_destroy.php">Выйти</a>
    </header>
    
    <h2>Мои заявки</h2>
    <?php if (isset($_GET['success'])): ?>
        <div style="color: green">Заявка успешно создана!</div>
    <?php endif; ?>
    
    <a href="new_request.php">➕ Новая заявка</a>

    <?php foreach ($requests as $request): ?>
        <div class="request-card">
            <p><strong>Адрес:</strong> <?= htmlspecialchars($request['address']) ?></p>
            <p><strong>Услуга:</strong> 
                <?= match($request['service_type']) {
                    'general' => 'Общий клининг',
                    'deep' => 'Генеральная уборка',
                    'post_construction' => 'Послестроительная уборка',
                    'carpet' => 'Химчистка ковров и мебели',
                    'other' => 'Иная: ' . htmlspecialchars($request['custom_service'])
                } ?>
            </p>
            <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($request['desired_datetime'])) ?></p>
            <p>
                <strong>Статус:</strong> 
                <span class="status <?= $request['status'] ?>">
                    <?= match($request['status']) {
                        'new' => 'Новая',
                        'in_progress' => 'В работе',
                        'completed' => 'Выполнено',
                        'canceled' => 'Отменено'
                    } ?>
                </span>
            </p>
            <?php if (!empty($request['admin_comment']) && $request['status'] !== 'new'): ?>
                <p class="comment">
                    <strong>Комментарий администратора:</strong> 
                    <?= htmlspecialchars($request['admin_comment']) ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>