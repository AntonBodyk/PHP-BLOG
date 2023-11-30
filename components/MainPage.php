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
    <title>Main Page</title>
</head>
<body>
<div class="navbar">
    <div>
        <p>Vue-Blog</p>
    </div>
    <div class="navbar-btns">
<!--        <span class="user-name"></span>-->
        <a href="SignPage.php">Войти</a>
    </div>
</div>
<?php if (!empty($postsArray)) : ?>
    <div>
        <h3>Список постов</h3>
        <?php foreach ($postsArray as $post) : ?>
            <div class="post-list">
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

</body>
</html>





