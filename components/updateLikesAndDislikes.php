<?php
require_once __DIR__ . "/../vendor/autoload.php";

use DataBaseClass\Connection\DataBase;
$dataBase = new DataBase();

$dataBaseConnect = $dataBase->getConnection();

if(!isset($_COOKIE['user_name'])){
    echo json_encode(['success' => false, 'message' => 'Войдите в аккаунт!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'like_post' || $_POST['action'] === 'dislike_post') {
        try {
            $postId = $_POST['post_id'];
            $userId = $_COOKIE['user_id'];

            $checkQuery = "SELECT * FROM user_ratings WHERE user_id = :user_id AND post_id = :post_id";
            $checkStmt = $dataBaseConnect->prepare($checkQuery);
            $checkStmt-> bindParam(':user_id', $userId);
            $checkStmt-> bindParam(':post_id', $postId);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'Вы уже оценили этот пост']);
                exit;
            }

            $updateField = ($_POST['action'] === 'like_post') ? 'likes_count' : 'dislikes_count';

            $updateQuery = "UPDATE posts SET $updateField = $updateField + 1 WHERE id = :post_id";
            $updateStmt = $dataBaseConnect->prepare($updateQuery);
            $updateStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $updateStmt->execute();

            $ratingsQuery = "INSERT INTO user_ratings (user_id, post_id, like_status, created_at) VALUES (:user_id, :post_id, :like_status, NOW())";
            $ratingsStmt = $dataBaseConnect->prepare($ratingsQuery);
            $likeStatus = ($_POST['action'] === 'like_post') ? 1 : -1;
            $ratingsStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $ratingsStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $ratingsStmt->bindParam(':like_status', $likeStatus, PDO::PARAM_INT);
            $ratingsStmt->execute();

            $selectQuery = "SELECT likes_count, dislikes_count FROM posts WHERE id = :post_id";
            $selectStmt = $dataBaseConnect->prepare($selectQuery);
            $selectStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $selectStmt->execute();

            $results = $selectStmt->fetchAll();

            echo json_encode(['success' => true, 'new_likes_count' => $results[0]['likes_count'], 'new_dislikes_count' => $results[0]['dislikes_count']]);
            exit;
        } catch (PDOException $e) {
            error_log('Ошибка базы данных: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    }
}