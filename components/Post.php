<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
use DataBaseClass\Connection\DataBase;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();
$post = null;
$postId = null;
$comments = null;


if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);


    $postQuery = "SELECT * FROM posts WHERE id = :postId";
    $postStmt = $dbConnect->prepare($postQuery);
    $postStmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $postStmt->execute();

    $post= $postStmt->fetch(PDO::FETCH_ASSOC);


    $commentsQuery = "SELECT * FROM comments WHERE post_id = :postId";
    $commentsStmt = $dbConnect->prepare($commentsQuery);
    $commentsStmt->bindParam(':postId', $postId);
    $commentsStmt->execute();

    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);


} else {
    echo 'Параметр ID не указан в URL.';
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] == "POST"){
        $comment_text = isset($_POST['comment_text']) ? $_POST['comment_text'] : null;
        $user_id = $_COOKIE['user_id'];
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;


        if(empty($comment_text)){
            $errors['comment_text'] = 'Заполните поле!';
        }elseif (!preg_match('/^[A-ZА-Я]/u', $comment_text)) {
            $errors['comment_text'] = 'Текст должен начинаться с заглавной буквы';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }else{
            try {
                $userNameQuery = "SELECT name FROM users WHERE id = :user_id";
                $userNameStmt = $dbConnect->prepare($userNameQuery);
                $userNameStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $userNameStmt->execute();
                $user = $userNameStmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    echo json_encode(['success' => false, 'message' => 'Пользователь не найден']);
                    exit();
                }


                $commentQuery = "INSERT INTO comments (comment_text, user_id, user_name, post_id, created_at, updated_at) VALUES (:comment_text, :user_id, :user_name, :post_id, NOW(), NOW())";
                $newComment = $dbConnect->prepare($commentQuery);
                $newComment->bindParam(':comment_text', $comment_text);
                $newComment->bindParam(':user_id', $user_id);
                $newComment->bindParam(':user_name', $user['name']);
                $newComment->bindParam(':post_id', $post_id);

                $newComment->execute();

            }catch (PDOException $e){
                $errors['message'] = 'Ошибка базы данных: ' . $e->getMessage();
                $_SESSION['errors'] = $errors;
//                header("Location: window.location.href"); // Перенаправление на страницу входа
//                exit();
            }
        }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])){
    if($_POST['action'] === 'delete_comment'){
        try{
            $commentId = $_POST['comment_id'];
            if (isset($_COOKIE['user_status']) && $_COOKIE['user_status'] === 'admin') {

                $deleteCommentQuery = "DELETE FROM comments WHERE id = :comment_id";
                $deleteCommentStmt = $dbConnect->prepare($deleteCommentQuery);
                $deleteCommentStmt->bindParam(':comment_id', $commentId, PDO::PARAM_INT);
                $deleteCommentStmt->execute();

                echo json_encode(['success' => true, 'message' => 'Комментарий успешно удален']);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Вы не являетесь администратором!']);
                exit();
            }
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css"  href="../static/css/Post.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Пост</title>
</head>
<body>
    <div class="main">
        <div class="post">
            <?php if($post) : ?>
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
            <?php else : ?>
                <p>Пост не найден.</p>
            <?php endif; ?>
        </div>

        <div class="comments">
            <?php if($comments) : ?>
                <ul class="comments-list">
                    <?php foreach ($comments as $comment) : ?>
                        <li class="comment">
                            <strong><?= $comment['user_name'] ?></strong>
                            <p>Дата создания: <?= $comment['created_at'] ?></p>
                            <p><?= $comment['comment_text'] ?></p>
                            <div class="comment-buttons">
                                <?php
                                if(isset($_COOKIE['user_status']) && $_COOKIE['user_status'] === 'admin'){
                                    echo '<button type="button" class="btn btn-danger delete-comment" data-comment-id="' . $comment['id'] . '">Удалить</button>';
                                }
                                ?>

                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="no-comments">Комментарии не найдены</p>
            <?php endif; ?>
        </div>

        <div class="add-new-comment">
                <h3>Добавьте свой комментарий</h3>
                <form class="comment-form" method="post" id="add-comment">
                    <div class="form-group">
                        <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                        <textarea class="form-control <?php echo isset($errors['comment_text']) ? 'is-invalid' : ''; ?>" id="comment" rows="4" placeholder="Добавьте комментарий" name="comment_text"><?php echo (isset($_POST['comment_text']) && !empty($errors)) ? htmlspecialchars($_POST['comment_text']) : ''; ?></textarea>
                        <?php if (isset($errors['comment_text'])) : ?>
                            <div class="invalid-feedback"><?php echo $errors['comment_text']; ?></div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary add-comment">Добавить</button>
                </form>
            </div>
        </div>


    <script>
        $('.delete-comment').click(function() {
            let commentId = $(this).data('comment-id');
            console.log('Before AJAX request');
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: { action: 'delete_comment', comment_id: commentId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log('AJAX success');

                        $(`.comment[data-comment-id="$commentId}"]`).remove();
                        location.reload();
                        alert('Комментарий успешно удален');
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