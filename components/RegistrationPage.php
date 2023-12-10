<?php
require_once __DIR__ . "/../vendor/autoload.php";

use DataBaseClass\Connection\Database;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'registration') {
        $name = isset($_POST["name"]) ? $_POST['name'] : null;
        $email = isset($_POST["email"]) ? $_POST['email'] : null;
        $password = isset($_POST["password"]) ? $_POST['password'] : null;
        $confirmPassword = isset($_POST["confirm_password"]) ? $_POST['confirm_password'] : null;

        $errors = [];

        if(empty($name)){
            $errors['name'] = 'Заполните поле!';
        }elseif (!preg_match('/^[A-ZА-Я]/u', $name)) {
            $errors['name'] = 'Имя повинно починатися з великої літери';
        }

        if(empty($email)){
            $errors['email'] = 'Заполните поле!';
        }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некоректний формат Email';
        }

        if(empty($password)){
            $errors['password'] = 'Заполните поле!';
        }elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password)) {
            $errors['password'] = 'Пароль повинен містити принаймні 6 символів і хоча б одну велику літеру';
        }

        if(empty($confirmPassword)){
            $errors['confirm_password'] = 'Заполните поле!';
        }elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Пароль і його підтвердження не співпадають';
        }

        header('Content-Type: application/json');

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn = $dbConnect;
            $query = "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, NOW(), NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error executing query']);
                exit();
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Помилка бази даних: ' . $e->getMessage()]);
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
    <link rel="stylesheet" href="../static/css/registration.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <title>Регистрация</title>
</head>
<body>

<div class="registration">
    <h1>Регистрация</h1>
    <form class="registration-form" method="post" id="registration">
        <div class="mb-3">
            <label for="exampleInputName" class="form-label">Имя</label>
            <input type="text" class="form-control" id="exampleInputName" name="name">
            <div class="invalid-feedback" id="name-error"></div>
        </div>

        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Email</label>
            <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp">
            <div class="invalid-feedback" id="email-error"></div>
        </div>

        <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="exampleInputPassword1" name="password">
            <div class="invalid-feedback" id="password-error"></div>
        </div>

        <div class="mb-3">
            <label for="exampleInputConfirmPassword1" class="form-label">Подтвердите пароль</label>
            <input type="password" class="form-control" id="exampleInputConfirmPassword1" name="confirm_password">
            <div class="invalid-feedback" id="confirm-password-error"></div>
        </div>

        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
</div>


<script>
    $(document).ready(function() {
        $('#registration').submit(function (event) {
            event.preventDefault();

            $('.invalid-feedback').text('');

            let form = $(this);
            $.ajax({
                url: 'RegistrationPage.php',
                type: 'POST',
                data: $('#registration').serialize() + '&action=registration',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        form.trigger('reset');
                        alert('Пользователь зарегистрирован!');

                        window.location.href = 'SignPage.php';
                    } else {
                        if (response.errors) {
                            if (response.errors.name) {
                                $('#name-error').text(response.errors.name).css('display', 'block');
                            }
                            if (response.errors.email) {
                                $('#email-error').text(response.errors.email).css('display', 'block');
                            }
                            if (response.errors.password) {
                                $('#password-error').text(response.errors.password).css('display', 'block');
                            }
                            if (response.errors.confirm_password) {
                                $('#confirm-password-error').text(response.errors.confirm_password).css('display', 'block');
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
                                if (response.errors.name) {
                                    $('#name-error').text(response.errors.name).css('display', 'block');
                                }
                                if (response.errors.email) {
                                    $('#email-error').text(response.errors.email).css('display', 'block');
                                }
                                if (response.errors.password) {
                                    $('#password-error').text(response.errors.password).css('display', 'block');
                                }
                                if (response.errors.confirm_password) {
                                    $('#confirm-password-error').text(response.errors.confirm_password).css('display', 'block');
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
