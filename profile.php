<?php

if (!empty($_POST)) {
    $errors = validate_form_fields($_POST);
    if (!empty($errors)) {
        foreach ($errors as $error) {
            print $error . '<br />';
        }
    } else {
        print 'Ваш профиль успешно обновлен.';
    }
}

// Инициализируем сессию
session_start();
// Проверяем, авторизован ли пользователь
$is_auth = $_SESSION['auth'] ?? false;
if (!$is_auth) {
    // Редирект на страницу входа
    header("Location: /index.php");
}
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_phone = $_SESSION['user_phone'];
$user_email = $_SESSION['user_email'];

function validate_form_fields(array $arr): array
{
    // массив с ошибками
    $errors = [];
    if (empty($arr['name'])) {
        $errors[] = 'Введите телефон или email!';
    }

    return $errors;
}
?>

<html>

<head>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<h1>Профиль</h1> <br />
<form method="POST">
    <input type="hidden" name="id" value="<?= $user_id ?>"><br />
    <input class="form-control" type="text" name="name" placeholder="Введите имя" value="<?= $user_name ?>"><br />
    <input class="form-control" type="text" name="phone" placeholder="Введите телефон" value="<?= $user_phone ?>"><br />
    <input class="form-control" type="email" name="email" placeholder="Введите email" value="<?= $user_email ?>"><br />
    <input class="form-control" type="password" name="password1" placeholder="Введите старый пароль"><br />
    <input class="form-control" type="password" name="password2" placeholder="Введите новый пароль"><br />
    <input type="submit" value="Сохранить">
</form>
</body>

</html>