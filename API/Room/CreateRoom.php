<?php

ini_set('display_errors', 1);
header('Access-Control-Allow-Origin:*');
header('Content-Type: application/json; charset=utf-8');

require_once '../../lib/Room.php';

require_once '../../lib/UserInfo.php';

$room = new Room();
$userInfo = new UserInfo();

if (false === $userInfo->CheckLogin()) {
    header('Error: Login failed.');
    http_response_code(403);

    exit;
}

// ユーザーが他の部屋に入っていないかチェック
$userID = $userInfo->CheckLogin()['userID'];
$gameInfo = $room->getGameInfo($userID);

if (false !== $gameInfo) {
    header('Error: The user is not in the room.');
    http_response_code(403);

    exit;
}

// 部屋を追加

if (isset($_POST['gamemode'])) {
    $roomID = $room->CreateRoomID();
    $gameID = $room->GetGameID() + 1;
    $playerID = 1;
    $gamemode = (string) $_POST['gamemode'];
    $room->AddRoom($gameID, $playerID, $userID, $roomID, 1, $gamemode);
    $playerInfo = $room->PlayerInfo($gameID, $playerID);
    $user = $userInfo->GetUserInfo($userID);
    $result = [
        'playerID' => $playerInfo['playerID'],
        'name' => $user['name'],
        'icon' => $user['icon'],
        'badge' => $user['badge'],
        'flag' => $playerInfo['flag'],
        'gamemode' => $gamemode,
    ];
    echo json_encode($result);
    http_response_code(200);

    exit;
}

header('Error: The requested value is different from the specified format.');
http_response_code(401);
