<?php

class mysqlo
{
    private $connection;

    function __construct($multi, string $l = "", string $p = "", string $db = "")
    {
        if ((!is_array($multi) && ($multi == "" || $l == "" || $db == "")) || (is_array($multi) && (($l != "" || $db != "") || count($multi) != 4))) {
            $t = debug_backtrace()[0];
            die("<br>Ошибка синтаксиса! Проверьте код в файле: " . $t["file"] . " , на строке " . $t["line"] . "<br>");
        }
        if (is_array($multi)) {
            $ip = $multi['ip'];
            $l = $multi['user'];
            $p = $multi['password'];
            $db = $multi['dbname'];
        } else {
            $ip = $multi;
        }
        try {
            $this->connect = new PDO("mysql:dbname=$db;host=$ip;charset=utf8;", $l, $p);
        } catch (PDOException $exception) {
            error_verify('adm');
        }
    }

    function startsWith($string, $startString)
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }

    function query(string $sql, array $parameters)
    {
        $statement = $this->connect->prepare($sql);
        $newParameters = [];
        foreach ($parameters as $key => $value) {
            if (!$this->startsWith($key, ':')) {
                $newParameters[':' . $key] = $value;
            } else {
                $newParameters[$key] = $value;
            }
        }
        $statement->execute($newParameters);
        if (false !== stripos($sql, 'SELECT'))
            return $statement->fetch(PDO::FETCH_ASSOC);
        return [];
    }
}