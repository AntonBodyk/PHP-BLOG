<?php
require_once '../classes/db.php';
$dataBaseConnect = connectToDataBase();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_post') {

        $title = isset($_POST['title']) ? $_POST['title'] : null;
        $body = isset($_POST['body']) ? $_POST['body'] : null;
        $category = isset($_POST['category']) ? $_POST['category'] : null;
        $user_id = isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : null;


        $errors = [];

        if (!preg_match('/^[A-ZА-Я]/', $title)) {
            $errors['title'] = 'Заголовок должен начинаться с заглавной буквы';
        }

        if (!preg_match('/^[A-ZА-Я]/', $body)) {
            $errors['body'] = 'Текст должен начинаться с заглавной буквы';
        }

        if (!preg_match('/^[A-ZА-Я]/', $category)) {
            $errors['category'] = 'Категория должна начинаться с заглавной буквы';
        }

        header('Content-Type: application/json');

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }

        try {
            $newPostQuery = "INSERT INTO posts (title, body, category, user_id, created_at, updated_at) VALUES (:title, :body, :category, :user_id, NOW(), NOW())";
            $newPost = $dataBaseConnect->prepare($newPostQuery);
            $newPost->bindParam(':title', $title);
            $newPost->bindParam(':body', $body);
            $newPost->bindParam(':category', $category);
            $newPost->bindParam(':user_id', $user_id);

            $newPost->execute();

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
}