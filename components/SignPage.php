<?php

require '../vendor/autoload.php';
require_once '../classes/db.php';
use Firebase\JWT\JWT;

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'sign') {
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;

    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный формат Email';
    }

    if (strlen($password) < 6 || !preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Пароль должен содержать не менее 6 символов и хотя бы одну заглавную букву';
    }

    header('Content-Type: application/json');

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }

    try {
        $conn = connectToDataBase();
        $query = $conn->prepare("SELECT id, name, role, password FROM users WHERE email = ?");
        $query->execute([$email]);

        $user = $query->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $remember_me_token = generateRandomToken();
            $update_token_query = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $update_token_query->execute([$remember_me_token, $user['id']]);
            $secret_key = bin2hex(random_bytes(32));
            $token = generateAuthorizationToken($user['id'], $secret_key, $user['name'], $user['role']);

            setcookie("token", $token, time() + 3600, "/");
            setcookie("user_name", $user['name'], time() + 3600, "/");
            setcookie("user_status", $user['role'], time() + 3600, "/");
            setcookie("user_id", $user['id'], time() + 3600, "/");
            setcookie("remember_me_token", $remember_me_token, time() + 86400 * 30, "/");

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'errors' => ['message' => 'Неверный email или пароль']]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
    exit();
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
      <form action="#" class="signin-form" method="post" id="sign">
          <div class="mb-3">
              <label for="exampleInputEmail1" class="form-label">Почта</label>
              <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required>
              <div class="invalid-feedback" id="email-error"></div>
          </div>
          <div class="mb-3">
              <label for="exampleInputPassword1" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
              <div class="invalid-feedback" id="password-error"></div>
          </div>
          <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="exampleCheck1">
              <label class="form-check-label" for="exampleCheck1">Запомнить меня</label>
          </div>
          <button type="submit" class="btn btn-primary">Войти</button>
      </form>
        <p class="registration">Нет аккаунта? <a href="RegistrationPage.php">Зарегистрироваться</a></p>

</div>

<script>
    $(document).ready(function() {
        $('#sign').submit(function (event) {
            event.preventDefault();

            $('.invalid-feedback').text('');

            let form = $(this);
            $.ajax({
                url: 'SignPage.php',
                type: 'POST',
                data: $('#sign').serialize() + '&action=sign',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        form.trigger('reset');
                        alert('Вход выполнен');

                        window.location.href = 'MainPage.php';
                    } else {
                        if (response.errors) {
                            if (response.errors.email) {
                                $('#email-error').text(response.errors.email).css('display', 'block');
                            }
                            if (response.errors.password) {
                                $('#password-error').text(response.errors.password).css('display', 'block');
                            }

                        } else {
                            alert('Произошла ошибка при регистрации: ' + response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);

                    if (xhr.responseText) {
                        try {
                            let response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                alert('Ошибка: ' + response.message);
                            }
                            if (response.errors) {
                                if (response.errors.email) {
                                    $('#email-error').text(response.errors.email).css('display', 'block');
                                }
                                if (response.errors.password) {
                                    $('#password-error').text(response.errors.password).css('display', 'block');
                                }

                            }
                        } catch (e) {
                            console.error('Ошибка при обработке JSON:', e);
                            alert('Произошла ошибка при обработке данных');
                        }
                    } else {
                        alert('Произошла неизвестная ошибка');
                    }
                }
            });
        });
    });
</script>
</body>
</html>