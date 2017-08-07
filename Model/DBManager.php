<?php

namespace Model;

use PDO;
use PDOException;

class DBManager
{
    private $dbh;

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new DBManager();
        return self::$instance;
    }

    private function __construct()
    {
        $this->dbh = null;
    }

    private function connectToDb()
    {
        global $config;
        $db_config = $config['db_config'];
        $dsn = 'mysql:dbname=' . $db_config['name'] . ';host=' . $db_config['host'];
        $user = $db_config['user'];
        $password = $db_config['pass'];

        try {
            $dbh = new PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            echo 'Connexion échouée : ' . $e->getMessage();
        }

        return $dbh;
    }

    protected function getDbh()
    {
        if ($this->dbh === null)
            $this->dbh = $this->connectToDb();
        return $this->dbh;
    }

    public function insert($table, $data = [])
    {
        $dbh = $this->getDbh();
        $query = 'INSERT INTO `' . $table . '` VALUES (NULL,';
        $first = true;
        foreach ($data AS $k => $value) {
            if (!$first)
                $query .= ', ';
            else
                $first = false;
            $query .= ':' . $k;
        }
        $query .= ')';
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        return true;
    }

    function findOne($query)
    {
        $dbh = $this->getDbh();
        $data = $dbh->query($query, PDO::FETCH_ASSOC);
        $result = $data->fetch();
        return $result;
    }

    function findOneSecure($query, $data = [])
    {
        $dbh = $this->getDbh();
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    function findAll($query)
    {
        $dbh = $this->getDbh();
        $data = $dbh->query($query, PDO::FETCH_ASSOC);
        $result = $data->fetchAll(r);
        return $result;
    }

    function findAllSecure($query, $data = [])
    {
        $dbh = $this->getDbh();
        $sth = $dbh->prepare($query);
        $sth->execute($data);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    function take_date()
    {
        $date = date("d-m-Y");
        $heure = date("H:i");
        return $date . " " . $heure;
    }

    public function getDatetimeNow()
    {
        date_default_timezone_set('Europe/Paris');
        return date("Y/m/d H:i:s");
    }

    /*
     * voir la doc add_date
     */
    public function add_date($givendate,$day=0,$mth=0,$yr=0) {
        $cd = strtotime($givendate);
        $newdate = date('Y-m-d h:i:s', mktime(date('h',$cd),
            date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
            date('d',$cd)+$day, date('Y',$cd)+$yr));
        return $newdate;
    }

    function watch_action_log($file, $text)
    {
        $file_log = fopen('logs/' . $file, 'a');
        fwrite($file_log, $text);
        fclose($file_log);
    }

    public function token()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < 20; $i++) {
            $randstring .= $characters[mt_rand(0, strlen($characters))];
        }
        return $randstring;
    }
}