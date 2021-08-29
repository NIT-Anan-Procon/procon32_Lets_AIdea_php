<?php

require_once('../Const.php');

class Word
{
    protected $dbh;

    public function __construct()
    {
        $dbname = db_name;
        $password = password;
        $user_name = db_user;

        $dsn = "mysql:host=localhost;dbname=$dbname;charset=utf8";

        try {
            $this->dbh = new PDO($dsn, $user_name, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            echo '接続失敗'.$e->getMessage();
            exit();
        };
    }

    public function AddWord($gameID, $playerID, $word, $flag)
    {
        $sql = "INSERT INTO word(gameID, playerID, word, flag)
        VALUES
            (:gameID, :playerID, :word, :flag)";

        try {
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindValue(':gameID', $gameID);
            $stmt->bindValue(':playerID', $playerID);
            $stmt->bindValue(':word', $word);
            $stmt->bindValue(':flag', $flag);
            $stmt->execute();
            $result = array('state'=>true);
            return $result;
        } catch (PDOException $e) {
            echo '接続失敗'.$e->getMessage();
            $result = array('state'=>'DBとの接続エラー');
            return $result;
            exit();
        }
    }

    public function GetWord($gameID, $playerID, $flag)
    {
        $stmt = $this->dbh->prepare("SELECT word FROM word WHERE gameID = :gameID AND playerID = :playerID AND flag = :flag");
        $stmt->bindValue(':gameID', $gameID);
        $stmt->bindValue(':playerID', $playerID);
        $stmt->bindValue(':flag', $flag);
        $stmt->execute();
        if ($flag == 2) {
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $result = $stmt->fetch(PDO::FETCH_COLUMN);
        }
        if ($result == false) {
            return null;
        }
        return $result;
    }

    public function Delword($gameID)
    {
        try {
            $stmt = $this->dbh->prepare("DELETE FROM word WHERE gameID = :gameID");
            $stmt->bindValue(':gameID', $gameID);
            $stmt->execute();
            $result = array('state'=>0);
            return $result;
        } catch (PDOException $e) {
            echo '接続失敗'.$e->getMessage();
            $result = array('state'=>'DBとの接続エラー');
            return $result;
            exit();
        }
    }
}
