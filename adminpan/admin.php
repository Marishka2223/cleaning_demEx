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

// Получение списка пользователей
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);

// Получение списка заявок с именами пользователей
$requests = $pdo->query("
    SELECT r.*, u.full_name, u.login 
    FROM requests r 
    JOIN users u ON r.user_id = u.user_id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Админ-панель</h1>

        <a href="../session_destroy.php" class="logout">Выйти</a>
        
        <h2>Пользователи</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Логин</th>
                    <th>ФИО</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['user_id']) ?></td>
                    <td><?= htmlspecialchars($user['login']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['user_id'] ?>" class="edit">Редактировать</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Заявки</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пользователь</th>
                    <th>Адрес</th>
                    <th>Телефон</th>
                    <th>Тип услуги</th>
                    <th>Способ оплаты</th>
                    <th>Дата/время</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request['request_id']) ?></td>
                    <td><?= htmlspecialchars($request['full_name']) ?> (<?= htmlspecialchars($request['login']) ?>)</td>
                    <td><?= htmlspecialchars($request['address']) ?></td>
                    <td><?= htmlspecialchars($request['phone']) ?></td>
                    <td>
                        <?= htmlspecialchars($request['service_type']) ?>
                        <?php if ($request['service_type'] == 'other' && !empty($request['custom_service'])): ?>
                            (<?= htmlspecialchars($request['custom_service']) ?>)
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($request['payment_type']) ?></td>
                    <td><?= htmlspecialchars($request['desired_datetime']) ?></td>
                    <td><?= htmlspecialchars($request['status']) ?></td>
                    <td>
                    <a href="edit_request.php?id=<?=$request['request_id'] ?>">Редактировать</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>