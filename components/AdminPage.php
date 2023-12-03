<?php
require_once '../classes/db.php';

$dbConnect = connectToDataBase();


$userAmount = 50;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $userAmount;

try {
    $usersQuery = "SELECT * FROM users LIMIT :limit OFFSET :offset";
    $stmt = $dbConnect->prepare($usersQuery);
    $stmt->bindParam(':limit', $userAmount, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();


    $usersArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}


try {
    $countQuery = "SELECT COUNT(*) as total FROM users";
    $countResult = $dbConnect->query($countQuery);
    $totalCount = $countResult->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}


$totalPages = ceil($totalCount / $userAmount);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_user') {
        // Дополнительные проверки безопасности

        try {
            $userId = $_POST['user_id'];
                // Удаление поста из базы данных
                $deleteUserQuery = "DELETE FROM users WHERE id = :user_id";
                $deleteUserStmt = $dbConnect->prepare($deleteUserQuery);
                $deleteUserStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                $deleteUserStmt->execute();

                // Отправляем успешный ответ
                echo json_encode(['success' => true, 'message' => 'Пользователь успешно удален']);
                exit;
        } catch (PDOException $e) {
            // Ошибка при выполнении запроса к базе данных
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../static/css/AdminPage.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <title>Админ-панель</title>
</head>
<body>
    <div class="admin-page">
        <h1>Админ-панель</h1>
        <?php foreach ($usersArray as $user) : ?>
            <div class="user">
                <p class="user-number">Номер: <?= $user['id'] ?></p>
                <p class="user-name">Имя пользователя: <?= $user['name'] ?></p>
                <button type="button" class="btn btn-danger delete-user" data-user-id="<?= $user['id'] ?>">Удалить</button>
            </div>
        <?php endforeach; ?>

        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($page = 1; $page <= $totalPages; $page++) : ?>
                    <li class="page-item <?php if ($page == $currentPage) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?= $page ?>"><?= $page ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>


    <script>
        $(document).ready(function() {
            $('.delete-user').click(function() {
                let userId = $(this).data('user-id');
                console.log('Before AJAX request');
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { action: 'delete_user', user_id: userId },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        if (response.success) {
                            console.log('AJAX success');
                            // Remove the deleted post from the UI
                            $(`.user[data-user-id="${userId}"]`).remove();
                            location.reload();
                            alert('Пользователь успешно удален');
                        } else {
                            console.log('Ошибка: ' + response.message);
                            alert('Ошибка: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Произошла ошибка при отправке запроса');
                    }
                });
            });
        });
    </script>

</body>
</html>
