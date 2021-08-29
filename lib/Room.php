<?php

require_once '../Const.php';

class Room
{
    protected $dbh;
    protected $table = 'room';

    public function __construct()
    {
        $dbname = db_name;
        $pass = password;
        $user = db_user;

        $dsn = "mysql:host=localhost;dbname={$dbname};charset=utf8";

        try {
            $this->dbh = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            echo '接続失敗'.$e->getMessage();

            exit();
        }
    }

    public function CreateRoomID()
    {
        $roomID = random_int(0, 9999);
        $code = (int) (sprintf('%04d', $roomID));
        $result = $this->RoomInfo($code);

        if (false === $result) {
            return $code;
        }
        $this->CreateRoomID();
    }

    public function GetGameID()
    {
        $stmt = $this->dbh->prepare("SELECT gameID FROM {$this->table} ORDER BY playerID DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (false === $result) {
            return 0;
        }

        return $result['gameID'];
    }

    public function GameInfo($gameID)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM {$this->table} WHERE gameID = :gameID");
        $stmt->bindValue(':gameID', $gameID);
        $stmt->execute();

        return $stmt->fetchall(PDO::FETCH_ASSOC);
    }

    public function PlayerInfo($playerID)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM {$this->table} WHERE playerID = :playerID");
        $stmt->bindValue(':playerID', $playerID);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function RoomInfo($roomID)
    {
        $st = $this->dbh->prepare("SELECT * FROM {$this->table} WHERE roomID = :roomID AND userID IS NULL");
        $st->bindValue(':roomID', $roomID);
        $st->execute();

        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function OwnerInfo($roomID)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM {$this->table} WHERE roomID = :roomID AND flag = :flag");
        $stmt->bindValue(':roomID', $roomID);
        $stmt->bindValue(':flag', 1);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getGameInfo($userID)
    {
        $stmt = $this->dbh->prepare("SELECT * FROM {$this->table} WHERE userID = :userID");
        $stmt->bindValue(':userID', $userID);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function AddRoom($userID, $roomID, $gameID, $flag)
    {
        $sql = "INSERT INTO {$this->table}(gameID, userID, roomID, flag)
        VALUES
            (:gameID, :userID, :roomID, :flag)";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':gameID', $gameID);
            $stmt->bindValue(':userID', $userID);
            $stmt->bindValue(':roomID', $roomID);
            $stmt->bindValue(':flag', $flag);
            $stmt->execute();
        } catch (PDOException $e) {
            return ['state' => 3];

            exit;
        }
    }

    public function JoinRoom($userID, $roomID)
    {
        $result = $this->RoomInfo($roomID);

        if (false !== $result) {
            $playerID = (int) ($result['playerID']);
            $this->dbh->beginTransaction();

            try {
                $stmt = $this->dbh->prepare("UPDATE {$this->table} SET userID = :userID WHERE playerID = :playerID");
                $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
                $stmt->bindValue(':playerID', $playerID, PDO::PARAM_INT);
                $stmt->execute();
                $this->dbh->commit();
                $result = $this->PlayerInfo($playerID);
                return $result;
            } catch (PDOException $e) {
                $this->dbh->rollBack();

                return ['state' => 3];

                exit;
            }
        } else {
            return ['state' => 2];
        }
    }

    public function DeleteRoom($gameID)
    {
        $stmt = $this->dbh->prepare("DELETE FROM {$this->table} WHERE gameID = :gameID");
        $stmt->bindValue(':gameID', $gameID);
        $stmt->execute();
    }

    public function LeaveRoom($playerID)
    {
        $result = $this->PlayerInfo($playerID);
        if (false !== $result) {
            $this->dbh->beginTransaction();

            try {
                $stmt = $this->dbh->prepare("UPDATE {$this->table} SET userID = null WHERE playerID = :playerID");
                $stmt->bindValue(':playerID', $playerID, PDO::PARAM_INT);
                $stmt->execute();
                $this->dbh->commit();
            } catch (PDOException $e) {
                return ['state' => 3];

                exit;
            }
        }
    }
}
