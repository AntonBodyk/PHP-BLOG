<?php
require_once __DIR__ . "/../vendor/autoload.php";
use DataBaseClass\Connection\DataBase;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();
$post = null;
$postId = null;
$comments = null;
$comment_id = null;

if (isset($_GET['id'])) {
    $postId = intval($_GET['id']);

    $postQuery = "SELECT * FROM posts WHERE id = :postId";
    $postStmt = $dbConnect->prepare($postQuery);
    $postStmt->bindParam(':postId', $postId, PDO::PARAM_INT);
    $postStmt->execute();

    $post = $postStmt->fetch(PDO::FETCH_ASSOC);

    $commentsQuery = "SELECT * FROM comments WHERE post_id = :postId";
    $commentsStmt = $dbConnect->prepare($commentsQuery);
    $commentsStmt->bindParam(':postId', $postId);
    $commentsStmt->execute();

    $comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);

} else {
    echo 'Параметр ID не указан в URL.';
}

$errors = [];
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])){
        if ($_POST['action'] === 'add_answer') {
                $answer_text = isset($_POST['answer_text']) ? $_POST['answer_text'] : null;
                $comment_id = isset($_POST['comment_id']) ? $_POST['comment_id'] : null;
                $userId = $_COOKIE['user_id'];

                if(empty($answer_text)){
                    $errors['answer_text'] = 'Заполните поле!';
                }elseif (!preg_match('/^[A-ZА-Я]/u', $answer_text)) {
                    $errors['answer_text'] = 'Заголовок должен начинаться с заглавной буквы';
                }

                header('Content-Type: application/json');

                if (!empty($errors)) {
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    exit();
                }

                try {
                    $userNameQuery = "SELECT name FROM users WHERE id = :user_id";
                    $userNameStmt = $dbConnect->prepare($userNameQuery);
                    $userNameStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
                    $userNameStmt->execute();
                    $user = $userNameStmt->fetch(PDO::FETCH_ASSOC);

                    // Добавление ответа на комментарий
                    $addAnswerQuery = "INSERT INTO comment_answers (answer_text, comment_id, user_id, user_name, created_at) VALUES (:answer_text, :comment_id,  :user_id, :user_name, NOW())";
                    $addAnswerStmt = $dbConnect->prepare($addAnswerQuery);
                    $addAnswerStmt->bindParam(':answer_text', $answer_text);
                    $addAnswerStmt->bindParam(':comment_id', $comment_id);
                    $addAnswerStmt->bindParam(':user_id', $userId);
                    $addAnswerStmt->bindParam(':user_name', $user['name']);
                    $addAnswerStmt->execute();

                    echo json_encode(['success' => true, 'message' => 'Ответ успешно добавлен']);
                    exit();
                }catch (PDOException $e){
                    $errorMessage = 'Ошибка базы данных: ' . $e->getMessage();
                    error_log($errorMessage);
                    echo json_encode(['success' => false, 'message' => $errorMessage]);
                    exit;
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
                            <?php
                                $answersQuery = "SELECT * FROM comment_answers WHERE comment_id = :comment_id";
                                $answersStmt = $dbConnect->prepare($answersQuery);
                                $answersStmt->bindParam(':comment_id', $comment['id'], PDO::PARAM_INT);
                                $answersStmt->execute();
                                $answers = $answersStmt->fetchAll(PDO::FETCH_ASSOC);

                                if ($answers) {
                                    echo '<ul class="answers-list">';
                                    foreach ($answers as $answer) {
                                        echo '<li class="answer">';
                                        echo '<strong>' . $answer['user_name'] . '</strong>';
                                        echo '<p>Дата создания: ' . $answer['created_at'] . '</p>';
                                        echo '<p>' . $answer['answer_text'] . '</p>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }


                            ?>
                            <div class="comment-buttons">
                                <button type="button" class="btn btn-primary add-answer" data-bs-toggle="modal" data-bs-target="#exampleModal-<?= $comment['id'] ?>" data-comment-id="<?= $comment['id'] ?>">
                                    Ответить
                                </button>
                                <?php
                                if(isset($_COOKIE['user_status']) && $_COOKIE['user_status'] === 'admin'){
                                    echo '<button type="button" class="btn btn-danger delete-comment" data-comment-id="' . $comment['id'] . '">Удалить</button>';
                                }
                                ?>
                                <div class="modal fade" id="exampleModal-<?= $comment['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Ответ</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form class="add-answer-form" method="post" data-comment-id="<?= $comment['id'] ?>">
                                                    <div class="mb-3">
                                                        <label for="exampleInputBody" class="form-label">Текст</label>
                                                        <textarea class="form-control answer-text" name="answer_text"></textarea>
                                                        <div class="invalid-feedback answer-error"></div>
                                                    </div>
                                                    <button type="button" class="btn btn-info new-answer">Добавить ответ</button>
                                                </form>
                                            </div>

                                        </div>
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
                        <div class="invalid-feedback" id="comment-error"></div>
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
        $('.add-comment').click(function (e) {
            e.preventDefault();

            $.ajax({
                url: 'add-comment.php',
                type: 'POST',
                data: $('#add-comment').serialize() + '&action=add_comment',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('Комментарий успешно добавлен');
                        location.reload();
                    } else {
                        if (response.errors) {
                            if (response.errors.comment_text) {
                                $('#comment-error').text(response.errors.comment_text).css('display', 'block');
                            }
                        }
                    }
                },
                error: function () {
                    alert('Произошла ошибка при отправке запроса');
                }
            });
        });
        $('.new-answer').click(function (e) {
            e.preventDefault();

            let form = $(this).closest('.add-answer-form');
            let commentId = form.data('comment-id');
            let answerText = form.find('.answer-text').val();
            let errorDiv = form.find('.answer-error');

            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'add_answer',
                    answer_text: answerText,
                    comment_id: commentId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert('Ответ успешно добавлен');
                        location.reload();
                    } else {
                        if (response.errors) {
                            if (response.errors.answer_text) {
                                errorDiv.text(response.errors.answer_text).css('display', 'block');
                            } else {
                                errorDiv.text('').css('display', 'none');
                            }
                        }
                    }
                },
                error: function () {
                    alert('Произошла ошибка при отправке запроса');
                }
            });
        });
    </script>
</body>
</html>