<?php

require_once '../../lib/UserInfo.php';

require_once '../../lib/UnsplashApi.php';
header('Access-Control-Allow-Origin:'.URL);
header('Access-Control-Allow-Credentials:true');

$userInfo = new UserInfo();
$unsplash = new UnsplashApi();

if (false === $userInfo->checkLogin()) {
    header('Error:Login failed.');
    http_response_code(403);

    exit;
}
$userID = $userInfo->checkLogin()['userID'];
$name = $_POST['name'];
$password = $_POST['password'];
$icon = $unsplash->getPhoto('');
if (isset($name)) {
    $result = $userInfo->changeUserName($userID, $name);
    if (false === $result['character']) {
        header('Error:Your name and password must be alphanumeric.');
        http_response_code(401);

        exit;
    }
    if (false === $result['name']) {
        header('Error:This name is already in use and cannot be used. You need to register with a different name.');
        http_response_code(401);

        exit;
    }
    if (false === $result['state']) {
        http_response_code(400);

        exit;
    }
}
if (isset($password)) {
    $result = $userInfo->changePassword($userID, $password);
    if (false === $result['character']) {
        header('Error:Your name and password must be alphanumeric.');
        http_response_code(401);

        exit;
    }
    if (false === $result['state']) {
        http_response_code(400);

        exit;
    }
}
if (isset($icon)) {
    $result = $userInfo->changeUserIcon($userID, $icon);
    if (false === $result) {
        http_response_code(400);

        exit;
    }
}
if (empty($result)) {
    header('Error:The requested value is different from the specified format.');
    http_response_code(401);

    exit;
}
http_response_code(200);
