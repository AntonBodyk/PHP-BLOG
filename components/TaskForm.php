<?php
interface ValidatorInterface {
    public function validate($data): bool;
}
class StringValidator implements ValidatorInterface {
    public function validate($data): bool {
        return is_string($data) && strlen($data) > 0;
    }
}

class IntegerValidator implements ValidatorInterface {
    public function validate($data): bool {
        return filter_var($data, FILTER_VALIDATE_INT) !== false;
    }
}

class FloatValidator implements ValidatorInterface {
    public function validate($data): bool {
        return filter_var($data, FILTER_VALIDATE_FLOAT) !== false;
    }
}

class ArrayValidator implements ValidatorInterface {
    public function validate($data): bool {
        return is_array($data) && count($data) > 0;
    }
}

class DateValidator implements ValidatorInterface {
    public function validate($data): bool {
        return strtotime($data) !== false;
    }
}
function displayError($field, $message) {
    echo "Помилка в полі \"$field\": $message <br>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nameValidator = new StringValidator();
    $ageValidator = new IntegerValidator();
    $heightValidator = new FloatValidator();
    $colorsValidator = new ArrayValidator();
    $dobValidator = new DateValidator();

    $name = $_POST["name"];
    $age = (int)$_POST["age"];
    $height = (float)$_POST["height"];
    $colors = explode(',', $_POST["colors"]);
    $dob = $_POST["dob"];

    if ($nameValidator->validate($name) &&
        $ageValidator->validate($age) &&
        $heightValidator->validate($height) &&
        $colorsValidator->validate($colors) &&
        $dobValidator->validate($dob)) {

        if (isset($_FILES["file"])) {
            $file_name = $_FILES["file"]["name"];
            $file_tmp = $_FILES["file"]["tmp_name"];
            move_uploaded_file($file_tmp, "uploads/" . $file_name);
        }

        echo "Ім'я: $name <br>";
        echo "Вік: $age <br>";
        echo "Зріст: $height <br>";
        echo "Кольори: " . implode(", ", $colors) . "<br>";
        echo "Дата народження: $dob <br>";

        if (isset($file_name)) {
            echo "Завантажений файл: $file_name <br>";
        }
    } else {
        if (!$nameValidator->validate($name)) {
            displayError('Ім\'я', 'Поле має бути непорожнім рядком');
        }
        if (!$ageValidator->validate($age)) {
            displayError('Вік', 'Поле має бути цілим числом');
        }
        if (!$heightValidator->validate($height)) {
            displayError('Зріст', 'Поле має бути дійсним числом');
        }
        if (!$colorsValidator->validate($colors)) {
            displayError('Кольори', 'Поле має бути масивом');
        }
        if (!$dobValidator->validate($dob)) {
            displayError('Дата народження', 'Поле має бути коректною датою');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма з різними типами даних</title>
</head>
<body>
<form method="post" enctype="multipart/form-data">
    <label for="name">Стрічка (String):</label>
    <input type="text" name="name" required><br>

    <label for="age">Ціле число (Integer):</label>
    <input type="number" name="age" required><br>

    <label for="height">Дійсне число (Float):</label>
    <input type="text" name="height" required><br>

    <label for="colors">Масив (Array):</label>
    <input type="text" name="colors" placeholder="червоний,зелений,синій" required><br>

    <label for="dob">Дата (Date):</label>
    <input type="date" name="dob" required><br>

    <label for="file">Файл:</label>
    <input type="file" name="file"><br>

    <input type="submit" value="Відправити">
</form>
</body>
</html>
