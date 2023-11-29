<?php
require '../vendor/autoload.php';
require_once '../classes/db.php';
use Faker\Factory as Faker;


$faker = Faker::create('en_US');
$dataBaseConnect = connectToDataBase();

//for ($i = 0; $i < 1000; $i++) {
//    $name = $faker->name;
//    $email = $faker->email;
//    $role = 'user';
//    $password = password_hash('qwerty', PASSWORD_DEFAULT); // Замініть це на свій власний пароль
//
//    // Для created_at і updated_at використовуйте поточний час
//    $now = new DateTime();
//    $created_at = $now->format('Y-m-d H:i:s');
//    $updated_at = $now->format('Y-m-d H:i:s');
//
//    $sql = "INSERT INTO users (name, email, role, password, created_at, updated_at) VALUES (
//        :name,
//        :email,
//        :role,
//        :password,
//        :created_at,
//        :updated_at
//    )";
//
//
//    $statement = $dataBaseConnect->prepare($sql);
//
//    $statement->execute(
//        ['name' => $name,
//        'email' => $email,
//        'role' => $role,
//        'password' => $password,
//        'created_at' => $created_at,
//        'updated_at' => $updated_at,
//    ]);
//}
//for ($i = 0; $i < 1000; $i++) {
//    $sql = "INSERT INTO posts (user_id, title, created_at, updated_at, category, body, likes_count, dislikes_count) VALUES (
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
//    $statement = $dataBaseConnect->prepare($sql);
//
//    $statement->execute([
//        'user_id' => $faker->numberBetween(1, 1000),
//        'title' => $faker->sentence,
//        'category' => $faker->word,
//        'body' => $faker->paragraph,
//        'likes_count' => $faker->numberBetween(0, 500),
//        'dislikes_count' => $faker->numberBetween(0, 100),
//    ]);
//}
try {
    for ($i = 0; $i < 1000; $i++) {
        $sql = "INSERT INTO posts (user_id, title, created_at, updated_at, category, body, likes_count, dislikes_count) VALUES (
        :user_id,
        :title,
        NOW(),
        NOW(),
        :category,
        :body,
        :likes_count,
        :dislikes_count
    )";

        $statement = $dataBaseConnect->prepare($sql);

        $statement->execute([
            'user_id' => $faker->numberBetween(1, 1000),
            'title' => $faker->sentence,
            'category' => $faker->word,
            'body' => $faker->paragraph,
            'likes_count' => $faker->numberBetween(0, 500),
            'dislikes_count' => $faker->numberBetween(0, 100),
        ]);
    }
} catch (PDOException $e) {
    echo 'Помилка бази даних: ' . $e->getMessage();
}


