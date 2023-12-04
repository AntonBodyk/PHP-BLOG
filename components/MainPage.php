<?php
require '../vendor/autoload.php';
require_once '../classes/db.php';
use Faker\Factory as Faker;


$faker = Faker::create('en_US');
$dataBaseConnect = connectToDataBase();


//try {
//    for ($i = 0; $i < 1000; $i++) {
//        $sql = "INSERT INTO posts (user_id, title, created_at, updated_at, category, body, likes_count, dislikes_count) VALUES (
//        :user_id,
//        :title,
//        NOW(),
//        NOW(),
//        :category,
//        :body,
//        :likes_count,
//        :dislikes_count
//    )";
//
//        $statement = $dataBaseConnect->prepare($sql);
//
//        $statement->execute([
//            'user_id' => $faker->numberBetween(1, 1000),
//            'title' => $faker->sentence,
//            'category' => $faker->word,
//            'body' => $faker->paragraph,
//            'likes_count' => $faker->numberBetween(0, 500),
//            'dislikes_count' => $faker->numberBetween(0, 100),
//        ]);
//    }
//} catch (PDOException $e) {
//    echo 'Помилка бази даних: ' . $e->getMessage();
//}
$postsPerPage = 50;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $postsPerPage;

try {
    $postsQuery = "SELECT * FROM posts LIMIT :limit OFFSET :offset";
    $stmt = $dataBaseConnect->prepare($postsQuery);
    $stmt->bindParam(':limit', $postsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();


    $postsArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}


try {
    $countQuery = "SELECT COUNT(*) as total FROM posts";
    $countResult = $dataBaseConnect->query($countQuery);
    $totalCount = $countResult->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}


$totalPages = ceil($totalCount / $postsPerPage);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    error_log('Получен AJAX-запрос');
    if ($_POST['action'] === 'delete_post') {
        // Дополнительные проверки безопасности
        try {
            $postId = $_POST['post_id'];
            if (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] === 'admin') {
                // Удаление поста из базы данных
                $deletePostQuery = "DELETE FROM posts WHERE id = :post_id";
                $deletePostStmt = $dataBaseConnect->prepare($deletePostQuery);
                $deletePostStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
                $deletePostStmt->execute();

                // Отправляем успешный ответ
                echo json_encode(['success' => true, 'message' => 'Пост успешно удален']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Вы не являетесь администратором!']);
                exit();
            }
        } catch (PDOException $e) {

            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    } elseif ($_POST['action'] === 'add_post') {
        try {
                $title = isset($_POST['title']) ? $_POST['title'] : null;
                $body = isset($_POST['body']) ? $_POST['body'] : null;
                $category = isset($_POST['category']) ? $_POST['category'] : null;
                $user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null;

                $newPostQuery = "INSERT INTO posts (title, body, category, user_id, created_at, updated_at) VALUES (:title, :body, :category, :user_id, NOW(), NOW())";
                $newPost = $dataBaseConnect->prepare($newPostQuery);
                $newPost->bindParam(':title', $title);
                $newPost->bindParam(':body', $body);
                $newPost->bindParam(':category', $category);
                $newPost->bindParam(':user_id', $user_id);

                $newPost->execute();

                echo json_encode(['success' => true, 'message' => 'Пост успешно добавлен']);
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
    <link rel="stylesheet" href="../static/css/MainPage.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <title>Main Page</title>
</head>
<body>
<div class="navbar">
    <div>
        <p>Vue-Blog</p>
    </div>
    <div class="navbar-btns">
        <?php

        if (isset($_COOKIE['user_name'])) {
            echo '<span class="user-name">' . urldecode($_COOKIE['user_name']) . '</span>';
            echo '<a class="logout" href="logout.php">Выход</a>';
            echo "<a href='AdminPage.php' onclick='checkAdminStatus(event)'>Админ-панель</a>";
        } else {
            echo '<a href="SignPage.php">Войти</a>';
            echo '<a href="#">Админ-панель</a>';
        }

        ?>
        <script>
            function checkAdminStatus(event) {
                event.preventDefault();
                $.post("checkAdminStatus.php", function (response) {
                    if (response === "admin") {
                        window.location.href = "AdminPage.php";
                    } else {
                        alert("Вы не являетесь админом!");
                    }
                });
            }
        </script>
    </div>
</div>
<?php if (!empty($postsArray)) : ?>
    <div class="main">
        <h3>Список постов</h3>
        <?php
            if(isset($_COOKIE['token'])) {
                echo '<button type="button" class="btn add-post" data-bs-toggle="modal" data-bs-target="#exampleModal">
                            Добавить пост
                       </button>';
            }
        ?>

        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Новый пост</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="add-post-form" method="post" id="add-post">
                            <div class="mb-3">
                                <label for="exampleInputTitle" class="form-label">Заголовок</label>
                                <input type="text" class="form-control" id="exampleInputTitle" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="exampleInputCategory" class="form-label">Категория</label>
                                <input type="text" class="form-control" id="exampleInputCategory" name="category" required>
                            </div>

                            <div class="mb-3">
                                <label for="exampleInputBody" class="form-label">Текст</label>
                                <textarea class="form-control" id="exampleInputBody" name="body"></textarea>
                            </div>

                            <button type="submit" class="btn new-post">Добавить</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
        <?php foreach ($postsArray as $post) : ?>
                <div class="post">
                    <h2 class="post-title"><?= $post['title'] ?></h2>
                    <div class="post-create-date">
                        Дата создания поста: <?= $post['created_at'] ?>
                    </div>
                    <div class="post-category">
                        Категория поста: <?= $post['category'] ?>
                    </div>
                    <div class="post-body">
                        <p><?= $post['body'] ?></p>
                    </div>
                    <div class="post-icons">
                        <div class="post-like">
                            <i class="fa-regular fa-thumbs-up" data-post-id="<?= $post['id'] ?>"></i>
                            <span class="like-count" data-post-id="<?= $post['id'] ?>"><?= $post['likes_count'] ?></span>
                        </div>
                        <div class="post-dislike">
                            <i class="fa-regular fa-thumbs-down" data-post-id="<?= $post['id'] ?>"></i>
                            <span class="dislike-count" data-post-id="<?= $post['id'] ?>"><?= $post['dislikes_count'] ?></span>
                        </div>
                        <div class="post-comment">
                            <i class="fa-regular fa-comment"></i>
                            <span>0</span>
                        </div>
                        <?php
                            if(isset($_COOKIE['user_status']) == 'admin'){
                                echo '<button type="button" class="btn btn-danger delete-post" data-post-id="' . $post['id'] . '">Удалить пост</button>';
                            }

                        ?>
                    </div>


            </div>
        <?php endforeach; ?>

        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php for ($page = 1; $page <= $totalPages; $page++) : ?>
                    <li class="page-item <?php if ($page == $current_page) echo 'active'; ?>">
                        <a class="page-link" href="?page=<?= $page ?>"><?= $page ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
<?php else : ?>
    <h2 class="error" style="color: red">Список постов пуст</h2>
<?php endif; ?>

<script>
    $(document).ready(function() {
        $('.delete-post').click(function() {
            let postId = $(this).data('post-id');
            console.log('Before AJAX request');
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { action: 'delete_post', post_id: postId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('AJAX success');

                        $(`.post[data-post-id="${postId}"]`).remove();
                        location.reload();
                        alert('Пост успешно удален');
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
        $('.post-like, .post-dislike').click(function() {
            let postId = $(this).find('i').data('post-id');
            let action = $(this).hasClass('post-like') ? 'like_post' : 'dislike_post';

            console.log('Post ID:', postId);
            console.log('Action:', action);

            $.ajax({
                url: 'updateLikesAndDislikes.php',
                type: 'POST',
                data: { action: action, post_id: postId },
                dataType: 'json',
                success: function(response) {
                    console.log('AJAX Success:', response);

                    if (response.success) {
                        $(`.like-count[data-post-id="${postId}"]`).text(response.new_likes_count);
                        $(`.dislike-count[data-post-id="${postId}"]`).text(response.new_dislikes_count);
                        location.reload();
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
        $('.logout').on('click', function() {

            $.ajax({
                url: 'logout.php',
                type: 'POST',
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        window.location.href = window.location.href;
                    }
                },
                error: function(error) {
                    console.error('Ошибка:', error);
                }
            });
        });
    });
    $('#add-post').submit(function(event) {
        event.preventDefault();

        let form = $(this);
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: $('#add-post').serialize() + '&action=add_post',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    form.trigger('reset');
                    $('#exampleModal').modal('hide');
                    alert('Пост успешно добавлен');
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

</script>

</body>
</html>





