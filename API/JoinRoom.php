<?php

ini_set('display_errors', 1);
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json; charset=utf-8');

require_once '../lib/Room.php';

require_once '../lib/UserInfo.php';

$room = new Room();
$userInfo = new UserInfo();

if (false === $userInfo->CheckLogin()) {
    http_response_code(403);

    exit;
}

if (filter_input(INPUT_POST, 'roomID')) {
    $userID = $userInfo->CheckLogin()['userID'];
    $roomID = (int) ($_POST['roomID']);

    $playerInfo = $room->JoinRoom($userID, $roomID);

    if (null === $playerInfo) {
        http_response_code(403);

        exit;
    }

    $user = $userInfo->GetUserInfo($userID);
    $result = [
        'playerID' => $playerInfo['playerID'],
        'name' => $user['name'],
        'icon' => $user['icon'],
        'badge' => $user['badge'],
        'flag' => $playerInfo['flag'],
        'gamemode' => $playerInfo['gamemode'],
    ];
    echo json_encode($result);
    http_response_code(200);

    exit;
}

http_response_code(401);
