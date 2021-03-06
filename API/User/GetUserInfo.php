<?php

require_once '../../lib/UserInfo.php';
header('Access-Control-Allow-Origin:'.URL);
header('Access-Control-Allow-Credentials:true');
header('Content-Type: application/json; charset=utf-8');

$userInfo = new UserInfo();
if (false === $userInfo->checkLogin()) {
    header('Error:Login failed.');
    http_response_code(403);

    exit;
}
$result = $userInfo->checkLogin();
if (false === $result['name']) {
    header('Error:User does not exist.');
    http_response_code(403);

    exit;
}
unset($result['userID'], $result['password']);

echo json_encode($result);
http_response_code(200);
