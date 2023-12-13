<?php
require_once __DIR__ . "/../vendor/autoload.php";
use DataBaseClass\Connection\DataBase;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();

$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

$userAmount = 50;
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($currentPage - 1) * $userAmount;

$filteredUsers = [];

try {
    $usersQuery = "SELECT * FROM users WHERE LOWER(name) LIKE :searchTerm LIMIT :limit OFFSET :offset";
    $stmt = $dbConnect->prepare($usersQuery);
    $searchTerm = '%' . strtolower($searchTerm) . '%';
    $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $userAmount, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $usersArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $filteredUsers = $usersArray;
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}

try {
    $countQuery = "SELECT COUNT(*) as total FROM users WHERE LOWER(name) LIKE :searchTerm";
    $countStmt = $dbConnect->prepare($countQuery);
    $countStmt->bindValue(':searchTerm', '%' . strtolower($searchTerm) . '%', PDO::PARAM_STR);
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}

$totalPages = ceil($totalCount / $userAmount);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_user') {
        try {
            $userId = $_POST['user_id'];

            $deleteUserQuery = "DELETE FROM users WHERE id = :user_id";
            $deleteUserStmt = $dbConnect->prepare($deleteUserQuery);
            $deleteUserStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $deleteUserStmt->execute();

            echo json_encode(['success' => true, 'message' => 'Пользователь успешно удален']);
            exit;
        } catch (PDOException $e) {
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Админ-панель</title>
</head>
<body>
<div class="admin-page">
    <h1>Админ-панель</h1>
    <div class="search-user">
        <form method="post">
            <input type="text" name="search" class="search" placeholder="Поиск по имени пользователя" required>
            <button type="submit" class="btn btn-primary">Поиск</button>
        </form>
    </div>

    <?php foreach ($filteredUsers as $user) : ?>
        <div class="user">
            <p class="user-number">Id: <?= $user['id'] ?></p>
            <p class="user-name">Имя пользователя: <?= $user['name'] ?></p>
            <button type="button" class="btn btn-danger delete-user" data-user-id="<?= $user['id'] ?>">Удалить</button>
        </div>
    <?php endforeach; ?>

    <?php if ($totalPages > 1) : ?>
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($page = 1; $page <= $totalPages; $page++) : ?>
                    <li class="page-item <?php if ($page == $currentPage) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?= $page ?>&search=<?= $searchTerm ?>"><?= $page ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script>
    $(document).ready(function() {
        // $('#searchInput').on('input', function() {
        //     let searchTerm = $(this).val().toLowerCase();
        //
        //     $('.user').each(function() {
        //         let userName = $(this).find('.user-name').text().toLowerCase();
        //         if (userName.includes(searchTerm)) {
        //             $(this).show();
        //         } else {
        //             $(this).hide();
        //         }
        //     });
        // });
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



