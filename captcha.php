<?php

$env = parse_ini_file('.env');
define("SMARTCAPTCHA_SERVER_KEY", $env['SMARTCAPTCHA_SERVER_KEY']);

/* Чтобы не повторяться в файлах login и register, вынес функцию в отдельный файл  */
function check_captcha($token): bool
{
    $ch = curl_init();
    $args = http_build_query([
        "secret" => SMARTCAPTCHA_SERVER_KEY,
        "token" => $token,
        "ip" => $_SERVER['REMOTE_ADDR'],
    ]);
    curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate?$args");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);

    $server_output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode !== 200) {
        echo "Разрешаем доступ из-за ошибки: код=$httpcode; сообщение=$server_output\n";
        return true;
    }
    $resp = json_decode($server_output);
    return $resp->status === "ok";
}