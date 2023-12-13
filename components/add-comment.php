<?php
require_once __DIR__ . "/../vendor/autoload.php";
use DataBaseClass\Connection\DataBase;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $comment_text = isset($_POST['comment_text']) ? $_POST['comment_text'] : null;
    $user_id = $_COOKIE['user_id'];
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : null;

    if (empty($comment_text)) {
        $errors['comment_text'] = 'Заполните поле!';
    } elseif (!preg_match('/^[A-ZА-Я]/u', $comment_text)) {
        $errors['comment_text'] = 'Текст должен начинаться с заглавной буквы';
    }

    header('Content-Type: application/json');

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }

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

        $jsonResponse = json_encode(['success' => true, 'message' => 'Пост успешно добавлен']);
        error_log('JSON Response: ' . $jsonResponse);
        echo $jsonResponse;
        exit;
    } catch (PDOException $e) {
        $errorMessage = 'Ошибка базы данных: ' . $e->getMessage();
        error_log($errorMessage);
        echo json_encode(['success' => false, 'message' => $errorMessage]);
        exit;
    }
}