<?php

require_once('captcha.php');

// Проверяю, отправил ли пользователь форму (массив $_POST не пустой)
if (!empty($_POST)) {
    $errors = validate_form_fields($_POST);
    if (!empty($errors)) {
        foreach ($errors as $error) {
            print $error . '<br />';
        }
    } else {
        $result = add_user_to_db($_POST);
        print $result . '<br />';
    }
}

function validate_form_fields(array $arr): array
{
    // Код валидации можно сделать красивее, например как во фреймворках,
    // но просили свою реализацию

    // массив с ошибками
    $errors = [];

    if (!empty($arr['name'])) {
        // Валидируем имя по ТЗ заказчика, например только буквы, можно использовать регулярные выражения
        if (!preg_match("/^[А-Я]+$/iu", $arr['name'])) {
            $errors[] = 'Имя содержит недопустимые символы!';
        }
    } else {
        $errors[] = 'Имя обязательно для заполнения!';
    }


    if (!empty($arr['phone'])) {
        // Валидируем телефон по ТЗ заказчика, например только цифры и знак плюс
        if (!preg_match("/^\+*[0-9]+$/", $arr['phone'])) {
            $errors[] = 'Телефон содержит недопустимые символы!';
        }
    } else {
        $errors[] = 'Поле телефон обязательно для заполнения!';
    }


    if (!empty($arr['email'])) {
        // Валидируем email
        if (!filter_var($arr['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email содержит недопустимые символы!';
        }
    } else {
        $errors[] = 'Поле email обязательно для заполнения!';
    }


    if (!empty($arr['password1'])) {
        // Валидируем пароль по ТЗ заказчика, например только буквы и цифры
        if (!preg_match("/^[a-z0-9]+$/iu", $arr['password1'])) {
            $errors[] = 'Пароль содержит недопустимые символы!';
        }
        if (!empty($arr['password2'])) {
            // В настоящих проектах нужно это выносить в отдельные методы
            // так как нарушаем принцип DRY
            if (!preg_match("/^[a-z0-9]+$/iu", $arr['password2'])) {
                $errors[] = 'Повтор пароля содержит недопустимые символы!';
            }
            if ($arr['password1'] !== $arr['password2']) {
                $errors[] = 'Пароли не совпадают!';
            }
        } else {
            $errors[] = 'Поле "Повтор пароля" обязательно для заполнения!';
        }
    } else {
        $errors[] = 'Поле пароль обязательно для заполнения!';
    }

    if (!empty($arr['smart-token'])) {
        if (!check_captcha($arr['smart-token'])) {
            $errors[] = 'Ошибка валидации капчи!';
        }
    } else {
        $errors[] = 'Подтвердите, что вы не бот!';
    }

    return $errors;
}

function add_user_to_db(array $user): string
{
    // Открываем соединение с SQlite с флагами на создание при необходимости, на чтение и запись
    $db = new SQLite3('database.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

    // Выводим ошибки в виде предупреждений
    $db->enableExceptions(true);

    // Создаем таблицу при необходимости
    $db->query('CREATE TABLE IF NOT EXISTS "users" (
        "id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        "name" VARCHAR,
        "phone" VARCHAR,
        "email" VARCHAR,
        "password" VARCHAR
    )');

    // Проверяем пользователя на наличие в БД
    $statement = $db->prepare('SELECT * FROM "users" WHERE "name" = ? OR "phone" = ? OR "email" = ?');
    $statement->bindValue(1, $user['name']);
    $statement->bindValue(2, $user['phone']);
    $statement->bindValue(3, $user['email']);
    $result = $statement->execute();
    if ($result->fetchArray()) {
        $db->close();
        return "Такой пользователь уже существует. Выполните вход.";
    }

    // Выполняем запрос на запись
    // В настоящих проектах сохраняем не пароль, а хэш пароля, чтобы при взломе не скомпроментировать
    // Обычно фреймворки предоставляют такую возможность, но если есть задача написать велосипед, то
    // можно использовать например md5() с "хвостиками" перед и после пароля и сверять хэш при логине

    // Подготавливаем SQL выражение
    $statement = $db->prepare('INSERT INTO "users" ("name", "phone", "email", "password")
        VALUES (:name, :phone, :email, :password)');
    // Биндим значения
    $statement->bindValue(':name', $user['name']);
    $statement->bindValue(':phone', $user['phone']);
    $statement->bindValue(':email', $user['email']);
    $statement->bindValue(':password', $user['password1']);
    $result = $statement->execute();
    $db->close();
    return "Вы зарегистрированы. Теперь вы можете выполнить вход.";
}

?>

<html>

<head>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h1>Регистрация</h1> <br />
    <h3>Уже зарегистрированы? Выполните <a href="/login.php">вход</a></h3><br />
    <form method="POST">
        <input class="form-control" type="text" name="name" placeholder="Введите имя"><br />
        <input class="form-control" type="text" name="phone" placeholder="Введите телефон"><br />
        <input class="form-control" type="email" name="email" placeholder="Введите email"><br />
        <input class="form-control" type="password" name="password1" placeholder="Введите пароль"><br />
        <input class="form-control" type="password" name="password2" placeholder="Введите повтор пароля"><br />
        <div id="captcha-container" class="smart-captcha form-control"
            data-sitekey="ysc1_C2af4768fb2rpDlUt9UYIk5wXLFXIlSgLxgB43rL8dcb0f8b" style="height: 100px"></div>
        <input type="submit" value="Регистрация">
    </form>
</body>

</html>