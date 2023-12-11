<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";

use DataBaseClass\Connection\DataBase;
use Firebase\JWT\JWT;

$dataBase = new DataBase();
$dbConnection = $dataBase->getConnection();

$errors = [];

function generateAuthorizationToken($user_id, $secret_key, $name, $role){
    $token_payload = array(
        "user_id" => $user_id,
        "user_name" => $name,
        "user_status" => $role,
        "exp_time" => time() + 3600
    );
    $authorizeToken = JWT::encode($token_payload, $secret_key, 'HS256');
    return $authorizeToken;
}

function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;

    if(empty($email)){
        $errors['email'] = 'Заполните поле!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный формат Email';
    }

    if(empty($password)){
        $errors['password'] = 'Заполните поле!';
    } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Пароль должен содержать не менее 6 символов и хотя бы одну заглавную букву';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    } else {
        try {
            $query = $dbConnection->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
            $query->execute([$email]);

            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $remember_me_token = generateRandomToken();
                $update_token_query = $dbConnection->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $update_token_query->execute([$remember_me_token, $user['id']]);
                $secret_key = bin2hex(random_bytes(32));
                $token = generateAuthorizationToken($user['id'], $secret_key, $user['name'], $user['role']);

                setcookie("token", $token, time() + 3600, "/");
                setcookie("user_name", $user['name'], time() + 3600, "/");
                setcookie("user_status", $user['role'], time() + 3600, "/");
                setcookie("user_id", $user['id'], time() + 3600, "/");
                setcookie("remember_me_token", $remember_me_token, time() + 86400 * 30, "/");


                header('Location: MainPage.php');
            } else {
                $errors['message'] = 'Неверный email или пароль';
                $_SESSION['errors'] = $errors;
                header("Location: SignPage.php"); // Перенаправление на страницу входа
                exit();
            }
        } catch (PDOException $e) {
            $errors['message'] = 'Ошибка базы данных: ' . $e->getMessage();
            $_SESSION['errors'] = $errors;
            header("Location: SignPage.php"); // Перенаправление на страницу входа
            exit();
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
    <link rel="stylesheet" type="text/css"  href="../static/css/sign.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Вход в аккаунт</title>
</head>
<body>

<div class="signin">
    <h1>Вход</h1>
    <?php if (isset($_SESSION['errors']['message'])) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $_SESSION['errors']['message']; ?>
        </div>
    <?php endif; ?>
    <form class="signin-form" method="post" id="sign">
        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Email</label>
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" value="<?php echo empty($errors) ? '' : htmlspecialchars(isset($_POST['email']) ? $_POST['email'] : ''); ?>">
            <?php if (isset($errors['email'])) : ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="exampleInputPassword" class="form-label">Пароль</label>
            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="exampleInputPassword" name="password" value="<?php echo empty($errors) ? '' : htmlspecialchars(isset($_POST['password']) ? $_POST['password'] : ''); ?>">
            <?php if (isset($errors['password'])) : ?>
                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
            <?php endif; ?>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1">
            <label class="form-check-label" for="exampleCheck1">Запомнить меня</label>
        </div>
        <button type="submit" class="btn btn-primary">Войти</button>
    </form>
    <p class="registration">Нет аккаунта? <a href="RegistrationPage.php">Зарегистрироваться</a></p>
</div>

</body>
</html>