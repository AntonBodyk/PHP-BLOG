<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="../static/css/registration.css">
    <title>Регистрация</title>
</head>
<body>
<?php
require_once '../classes/db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? $_POST['name'] : null;
    $email = isset($_POST["email"]) ? $_POST['email'] : null;
    $password = isset($_POST["password"]) ? $_POST['password'] : null;
    $confirmPassword = isset($_POST["confirm_password"]) ? $_POST['confirm_password'] : null;

    // Валідація
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Поле "Ім\'я" не може бути порожнім';
    }elseif (!preg_match('/[A-ZА-Я]/', $name)){
        $errors[] = '"Ім\'я" повинно починатися з великої літери';
    }

    if (empty($email)) {
        $errors[] = 'Поле "Email" не може бути порожнім';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некоректний формат Email';
    }

    if (empty($password)) {
        $errors[] = 'Поле "Пароль" не може бути порожнім';
    } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Пароль повинен містити принаймні 6 символів і хоча б одну велику літеру';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Пароль і його підтвердження не співпадають';
    }

    // Перевірка наявності помилок
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
        }
        exit();
    }


    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {

        $conn = connectToDatabase();


        $query = "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);


        if ($stmt->execute()) {
            echo "Data inserted successfully";
        } else {
            echo "Error executing query";
            print_r($stmt->errorInfo());
        }


        $redirect_url = "http://localhost:63342/php-blog/components/SignPage.php";
        error_log("Redirecting to SignPage");
        header("Location: " . $redirect_url);
        exit();

    } catch (PDOException $e) {
        echo 'Помилка бази даних: ' . $e->getMessage();
    }
}
?>
<div class="registration">
    <h1>Регистрация</h1>
    <form class="registration-form" method="post">
        <div class="mb-3">
            <label for="exampleInputName" class="form-label">Имя</label>
            <input type="text" class="form-control" id="exampleInputName" name="name" required>
        </div>

        <div class="mb-3">
            <label for="exampleInputEmail1" class="form-label">Email</label>
            <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required>
            <div class="invalid-feedback">
                Введите корректный адрес электронной почты.
            </div>
        </div>

        <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
        </div>

        <div class="mb-3">
            <label for="exampleInputConfirmPassword1" class="form-label">Подтвердите пароль</label>
            <input type="password" class="form-control" id="exampleInputConfirmPassword1" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
    </form>
</div>



</body>
</html>
