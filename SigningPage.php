<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./static/css/style.css">
    <title>Вход в аккаунт</title>
</head>
<body>
  <div class="signin">
      <h1>Вход</h1>
      <form action="#" class="signin-form">
          <div class="mb-3">
              <label for="exampleInputEmail1" class="form-label">Почта</label>
              <input type="email" class="form-control" id="exampleInputEmail1" name="email" aria-describedby="emailHelp" required>
          </div>
          <div class="mb-3">
              <label for="exampleInputPassword1" class="form-label">Пароль</label>
              <input type="password" class="form-control" id="exampleInputPassword1" name="email" required>
          </div>
          <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="exampleCheck1">
              <label class="form-check-label" for="exampleCheck1">Запомнить меня</label>
          </div>
          <button type="submit" class="btn btn-primary">Войти</button>
      </form>

  </div>
</body>
</html>