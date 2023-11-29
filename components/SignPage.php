<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"  href="../static/css/sign.css">
    <title>Вход в аккаунт</title>
</head>
<body>
    <?php
        require '../vendor/autoload.php';
        require_once  '../classes/db.php';
        use Firebase\JWT\JWT;
        phpinfo();
        function getAuthorizationToken($user_id, $secret_key){
            $token_payload = array(
                "user_id" => $user_id,
                "exp_time" => time() + 3600
            );
            $authorizeToken = JWT::encode($token_payload, $secret_key, 'HS256');
            return $authorizeToken;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = isset($_POST['email']) ? $_POST['email'] : null;
            $password = isset($_POST['password']) ? $_POST['password'] : null;


            $errors = [];

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

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    echo '<div class="alert alert-danger" role="alert">' . $error . '</div>';
                }
                exit();
            }
            $conn = connectToDataBase();

            // Используем параметризованный запрос для предотвращения SQL-инъекций
            $query = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
            $query->execute([$email]);

            // Используем fetch вместо rowCount
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Пароль верен
                $secret_key = bin2hex(random_bytes(32)); // Замените на ваш секретный ключ
                $token = getAuthorizationToken($user['id'], $secret_key);

                // Устанавливаем cookie
                setcookie("token", $token, time() + 3600, "/");

                // Выводим сообщение в консоль браузера
                echo "<script>console.log('Cookie установлено успешно');</script>";
                // Выводим JSON-ответ
//                echo json_encode(["token" => $token]);
            } else {
                // Пользователь не найден или пароль неверен
                echo "<script>alert('Зарегистрируйтесь!Или проверте введеные данные!');</script>";
            }
        }
    ?>
    <div class="signin">
      <h1>Вход</h1>
      <form action="#" class="signin-form" method="post">
          <div class="mb-3">
              <label for="exampleInputEmail1" class="form-label">Почта</label>
              <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required>
          </div>
          <div class="mb-3">
              <label for="exampleInputPassword1" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="exampleInputPassword1" name="password" required>
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