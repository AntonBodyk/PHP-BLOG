<?php
session_start();

require_once __DIR__ . "/../vendor/autoload.php";

use DataBaseClass\Connection\Database;

$dataBase = new DataBase();
$dbConnect = $dataBase->getConnection();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST["name"]) ? $_POST['name'] : null;
    $email = isset($_POST["email"]) ? $_POST['email'] : null;
    $password = isset($_POST["password"]) ? $_POST['password'] : null;
    $confirmPassword = isset($_POST["confirm_password"]) ? $_POST['confirm_password'] : null;

    if (empty($name)) {
        $errors['name'] = 'Заполните поле!';
    } elseif (!preg_match('/^[A-ZА-Я]/u', $name)) {
        $errors['name'] = 'Имя повинно починатися з великої літери';
    }

    if (empty($email)) {
        $errors['email'] = 'Заполните поле!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некоректний формат Email';
    }

    if (empty($password)) {
        $errors['password'] = 'Заполните поле!';
    } elseif (strlen($password) < 6 || !preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Пароль повинен містити принаймні 6 символів і хоча б одну велику літеру';
    }

    if (empty($confirmPassword)) {
        $errors['confirm_password'] = 'Заполните поле!';
    } elseif ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Пароль і його підтвердження не співпадають';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn = $dbConnect;
            $query = "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, NOW(), NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                header('Location: SignPage.php');
                exit();
            } else {
                $errors['registration'] = 'Ошибка запроса';
            }
        } catch (PDOException $e) {
            $errors['database'] = 'Ошибка базы данных: ' . $e->getMessage();
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
    <?php if (!empty($successMessage)) : ?>
        <?php echo "<script>alert('Регистрация успешна!')</script>" ?>
    <?php endif; ?>
    <form class="registration-form" method="post" id="registration">
        <div class="mb-3">
            <label for="exampleInputName" class="form-label">Имя</label>
            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="exampleInputName" name="name" value="<?php echo empty($errors) ? '' : htmlspecialchars(isset($_POST['name']) ? $_POST['name'] : ''); ?>">
            <?php if (isset($errors['name'])) : ?>
                <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
            <?php endif; ?>
        </div>

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

        <div class="mb-3">
            <label for="exampleInputPasswordConfirm" class="form-label">Подтвердите пароль</label>
            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="exampleInputPasswordConfirm" name="confirm_password" value="<?php echo empty($errors) ? '' : htmlspecialchars(isset($_POST['confirm_password']) ? $_POST['confirm_password'] : ''); ?>">
            <?php if (isset($errors['confirm_password'])) : ?>
                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
        <?php
        if (empty($errors) && isset($_POST)) {
            $_POST = array();
        }
        ?>
    </form>

</div>
</body>
</html>
