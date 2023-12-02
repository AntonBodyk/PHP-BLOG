<?php
require_once '../classes/db.php';
$dataBaseConnect = connectToDataBase();
if(!isset($_COOKIE['user_name'])){
    echo json_encode(['success' => false, 'message' => 'Войдите в аккаунт!']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'like_post' || $_POST['action'] === 'dislike_post') {
        try {
            $postId = $_POST['post_id'];

            // Определите, какое поле нужно обновить
            $updateField = ($_POST['action'] === 'like_post') ? 'likes_count' : 'dislikes_count';

            // Выполните SQL-запрос для увеличения значения на единицу
            $updateQuery = "UPDATE posts SET $updateField = $updateField + 1 WHERE id = :post_id";
            $updateStmt = $dataBaseConnect->prepare($updateQuery);
            $updateStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
            $updateStmt->execute();

            // Получите новые значения лайков или дизлайков
            $newLikesCount = ($_POST['action'] === 'like_post') ? $_POST['likes_count'] + 1 : $_POST['likes_count'];
            $newDislikesCount = ($_POST['action'] === 'dislike_post') ? $_POST['dislikes_count'] + 1 : $_POST['dislikes_count'];

            // Отправляем успешный ответ
            echo json_encode(['success' => true, 'new_likes_count' => $newLikesCount, 'new_dislikes_count' => $newDislikesCount]);
            exit;
        } catch (PDOException $e) {
            // Ошибка при выполнении запроса к базе данных
            error_log('Ошибка базы данных: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
            exit;
        }
    }
}