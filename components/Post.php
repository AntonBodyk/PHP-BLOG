<?php
include_once '../classes/db.php';
$dbConnect = connectToDataBase();
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
                        <textarea class="form-control" id="comment" rows="4" placeholder="Добавьте комментарий" name="comment_text"></textarea>
                        <div class="invalid-feedback error" id="text-error"></div>
                    </div>
                    <button type="submit" class="btn btn-primary add-comment">Добавить</button>
                </form>
            </div>
        </div>


    <script>
        $('#add-comment').submit(function (event) {
            event.preventDefault();

            // Сброс предыдущих ошибок
            $('.invalid-feedback').text('');

            let form = $(this);
            $.ajax({
                url: 'add-comment.php',
                type: 'POST',
                data: $('#add-comment').serialize() + '&action=add_comment',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        form.trigger('reset');
                        // alert('Пост успешно добавлен');

                        location.reload();
                    } else {
                        if (response.errors) {
                            if (response.errors.comment_text) {
                                $('#text-error').text(response.errors.comment_text).css('display', 'block');
                            }

                        } else {
                            alert('Произошла ошибка при добавлении поста: ' + response.message);
                        }
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);

                    if (xhr.responseText) {
                        console.log('Response Text:', xhr.responseText);
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                alert('Ошибка: ' + response.message);
                            }
                            if (response.errors) {
                                if (response.errors.comment_text) {
                                    $('#text-error').text(response.errors.comment_text);
                                }

                            }
                        } catch (e) {
                            console.error('Ошибка при обработке JSON:', e);
                            alert('Произошла ошибка при обработке данных');
                        }
                    } else {
                        console.error('Произошла неизвестная ошибка', response);
                        alert('Произошла неизвестная ошибка');
                    }
                }
            });
        });
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