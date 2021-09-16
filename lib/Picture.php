<?php

require_once __DIR__.'/../Const.php';

class Picture
{
    protected $dbh;
    protected $table = 'picture';

    public function __construct()
    {
        $dbname = db_name;
        $password = password;
        $user_name = db_user;
        $dsn = "mysql:host=localhost;dbname={$dbname};charset=utf8";

        try {
            $this->dbh = new PDO($dsn, $user_name, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            header('Error:'.$e->getMessage());

            exit();
        }
    }

    public function AddPicture($gameID, $playerID, $PictureUrl, $answer)
    {
        $sql = "INSERT INTO {$this->table}(gameID, playerID, pictureURL, answer)
        VALUES
            (:gameID, :playerID, :pictureURL, :answer)";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':gameID', $gameID);
            $stmt->bindValue(':playerID', $playerID);
            $stmt->bindValue(':pictureURL', $PictureUrl);
            $stmt->bindValue(':answer', $answer);
            $stmt->execute();
        } catch (PDOException $e) {
            header('Error:'.$e->getMessage());

            exit();
        }
    }

    public function GetPicture($gameID, $playerID, $answer)
    {
        $sql = "SELECT pictureURL, answer FROM {$this->table} WHERE gameID = :gameID AND playerID ";
        if (null === $playerID) {
            $sql .= 'IS NULL';
        } else {
            $sql .= '= :playerID';
        }
        if (0 < $answer) {
            $sql .= ' AND answer = :answer';
        }
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':gameID', $gameID);
        if (null !== $playerID) {
            $stmt->bindValue(':playerID', $playerID);
        }
        if (0 < $answer) {
            $stmt->bindValue(':answer', $answer);
        }
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (0 < $answer) {
            if (!isset($result[0])) {
                $result[0]['pictureURL'] = null;
                $result[0]['answer'] = $answer;
            }
        } else {
            $result = $this->CheckPicture($result);
        }

        return $result;
    }

    public function CheckPicture($result)
    {
        if (!isset($result[0])) {
            $count = 0;
        } else {
            $count = count($result);
        }
        for ($i = $count; $i < 4; ++$i) {
            $result[$i]['pictureURL'] = null;
            $result[$i]['answer'] = 0;
        }

        return $result;
    }

    public function deleteGameInfo($gameID)
    {
        $stmt = $this->dbh->prepare("DELETE FROM {$this->table} WHERE gameID = :gameID");
        $stmt->bindValue(':gameID', $gameID);
        $stmt->execute();
    }

    public function getPictures($gameID, $playerID)
    {
        $sql = "SELECT * FROM {$this->table} WHERE gameID = :gameID AND playerID = :playerID";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':gameID', $gameID);
        $stmt->bindValue(':playerID', $playerID);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLearnPhoto($gameID, $answer)
    {
        $sql = "SELECT pictureURL FROM {$this->table} WHERE gameID = :gameID AND answer = :answer";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(':gameID', $gameID);
        $stmt->bindValue(':answer', $answer);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
}
