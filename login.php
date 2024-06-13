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
        $result = do_login($_POST);
    }
}

function validate_form_fields(array $arr): array
{
    // массив с ошибками
    $errors = [];
    if (empty($arr['login'])) {
        $errors[] = 'Введите телефон или email!';
    }
    if (empty($arr['password'])) {
        $errors[] = 'Введите пароль!';
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

function do_login(array $user): string|null
{
    // Открываем соединение с SQlite с флагами на создание при необходимости, на чтение и запись
    $db = new SQLite3('database.sqlite', SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

    // Выводим ошибки в виде предупреждений
    $db->enableExceptions(true);

    // Проверяем пользователя на наличие в БД
    $statement = $db->prepare('SELECT * FROM "users" WHERE ("phone" = ? OR "email" = ?) AND "password" = ?');
    $statement->bindValue(1, $user['login']);
    $statement->bindValue(2, $user['login']);
    $statement->bindValue(3, $user['password']);
    $result = $statement->execute();
    if ($user = $result->fetchArray()) {
        // Пользователь найден
        // Инициализируем сессию
        session_start();
        // Записываем состояние авторизации
        $_SESSION['auth'] = true;
        // И параметры пользователя
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_email'] = $user['email'];
        // Редирект в профиль
        header("Location: /profile.php");
    } else {
        return 'Пользователь с таким логином и паролем не найден.';
    }
}
?>

<html>

<head>
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
    <h1>Вход на портал</h1> <br />
    <h3>Нужна регистрация? <a href="/register.php">Зарегистрируйтесь</a>.</h3><br />
    <form method="POST">
        <input class="form-control" type="text" name="login" placeholder="Введите телефон или email"><br />
        <input class="form-control" type="password" name="password" placeholder="Введите пароль"><br />
        <div id="captcha-container" class="smart-captcha form-control"
             data-sitekey="ysc1_C2af4768fb2rpDlUt9UYIk5wXLFXIlSgLxgB43rL8dcb0f8b" style="height: 100px">
        </div>
        <input type="submit" value="Вход">
    </form>
    </body>
</html>