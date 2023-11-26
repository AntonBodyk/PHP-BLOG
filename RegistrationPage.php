<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,400;1,300&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./static/css/registration.css">
    <title>Регистрация</title>
</head>
<body>

<div class="registration">
    <h1>Регистрация</h1>
    <form action="#" class="registration-form">
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
