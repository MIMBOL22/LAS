<?php
//ini_set('error_reporting', E_ERROR);

$config = [
    'teh' => 0,                                         //  0 - Нет, 1 - Да                                                       Включить ли тех.работы?
    'launcher'=>[
        'type' => 0,                                    //  0 - request, 1 - json                                                 Какой у вас тип авторизации?
        'user' => 'login',                              //                                                                        Какой ключ запроса отвечает за имя    пользвотвателя? пример для request: ?LOGIN=%login%
        'pass' => 'password',                           //                                                                        Какой ключ запроса отвечает за пароль пользвотвателя? пример для request: &PASSWORD=%password%
        'key' => "",                                    // Ключ проверки лаунчера, в ссылку надо будет добавить ?key=(ключ) (оставить пустым чтобы отключить)
    ],
    'additions' => [                                                        // Дополнения
        'type_ban' => 0,                                // 0 - Отключена, 1 - Столбец у юзера, 2 - Отдельная таблица              Какая у вас система банов?
        'er' => 0,                                      // 0 - Нет, 1 - Да                                                        Выводить ли причину бана?
        'type_whi' => 0,                                // 0 - Отключена, 1 - Столбец у юзера, 2 - Отдельная таблица              Какая у вас система вайтлиста?
    ],
    'database' => [                                                           // База данных
        'user' => "",                                   // Имя пользователя в базе данных
        'password' => "",                               // Пароль пользователя в базе данных
        'ip' => "",                                     // IP базы данныз
        'dbname' => ""                                  // Имя базы данных
    ],
    'password' => [                                                           // Система пароля
        'system' => 0,                                  // 0 - WebMCR , 1 - DLE , 2 - Другая                                      Какая у вас CMS?
        'dle' => 0,                                     // 0 - 11.1 и меньше, 4 - 11.2 и выше                                     Какая у вас версия DLE? (Если у вас не DLE не трогайте)
        'webmcr' => 0,                                  // 0-MD5, 1-Double MD5, 3-md5(md5(pass)+salt)                             Какой у вас хэш пароля? (Меняйте только если у вас WebMCR)
        'other_hash' => 2,                              // 0-MD5, 1-Double MD5, 2-md5(pass+salt),3-md5(md5(pass)+salt),4-bcrypt   Какой у вас хэш пароля? (Если у вас DLE или WebMCR не трогайте)
    ],
    'table' => [                                                            // Если у вас не DLE и не WebMCR, укажите, какой столбец за что отвечает
        'name' => "",                                   // Имя таблицы с пользователями
        'username' => '',                               // Столбец с именем пользователя
        'salt' => '',                                   // Столбец с солью пароля
        'password' => '',                               // Столбец с паролем пользователя
        'email' => '',                                  // Столбец с почтой пользователя
        'ban' => '',                                    // Столбец с колоткой бана (1 - забанен, 0 - нет)         (только если у вас первая система бана)
        'bt' => '',                                     // Столбец с временем бана (unix время до какого времени) (только если у вас первая система бана)
        'br' => '',                                     // Столбец с причиной бана (текстом)                      (только если у вас первая система бана)
        'wh' => 'white'                                 // Столбец с колоткой вайтлиста (1 - добавлен , 0 - нет)  (только если у вас первая система бана)
    ],
    'ban' => [                                                              // Таблица с банлистом (если у вас вторая система бана)
        'name' => '',                                   // Имя таблицы с банлистом
        'username' => '',                               // Столбец с именем пользователя
        'reason' => '',                                 // Столбец с причиной бана
        'time' => ''                                    // Столбец с временем бана (unix время до какого времени)
    ],
    'wht' => [                                                              // Таблица с вайтлистом (если у вас вторая система бана)
        'name' => '',                                   // Имя таблицы с вайтлистом
        'username' => 'u',                              // Столбец с именем пользователя
    ],
    'mess' => [                                                             // Сообщения при ошибки
        'pas' => 'Неверный логин или пароль!',          // Ошибка неправильного пароля
        'ban' => 'Извините, но вы забанены',            // Ошибка при бане
        'adm' => 'Ошибка, обратитесь к админам',        // Ошибка при ошибки в коде
        'teh' => 'Лаунчер закрыт на тех. работы',       // Ошибка при тех. работах
        'wht' => 'Вы не добавлены в вайтлист',          // Ошибка при отсутствии игрока в вайтлисте
        'unk' => 'Неизвестная ошибка!'                  // Ошибка при неизвестной ошибки
    ]
];
if($_GET['mimbol'] != ""){                              // Не трогайте, версия скрипта
    echo 'mimbol 1.1';                                  // Не трогайте, версия скрипта
}
if($config['teh'] == 1)
    err('teh');
if($_GET['key'] != $config['additions']['key'])
    err('adm');
class mysqlo
{
    private $connection;

    function __construct(string $ip, string $l, string $p, string $db)
    {
        try {
            $this->connect = new PDO("mysql:dbname=$db;host=$ip;charset=utf8;", $l, $p);

        } catch (PDOException $exception) {
            err('adm');
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

function err(string $mess)
{
    if($GLOBALS['config']['type'] == 1){
        die(json_encode(['error' => $GLOBALS['config']['mess'][$mess]]));
    }else{
        die($GLOBALS['config']['mess'][$mess]);
    }
}
function ban($reas){


    if($GLOBALS['config']['type'] == 1){
        if($GLOBALS['config']['additions']['er'] == 1)
            $res = " по причине ".$reas;
        die(json_encode(['error' =>$GLOBALS['config']['mess']['ban'].$res]));
    }else{
        if($GLOBALS['config']['additions']['er'] == 1)
            $res = " по причине ".$reas;
        die($GLOBALS['config']['mess']['ban'].$res);
    }

}
$mysql = new mysqlo(
    $config['database']['ip'],
    $config['database']['user'],
    $config['database']['password'],
    $config['database']['dbname']
);
if($GLOBALS['config']['type'] == 1) {
    $u = $_POST[$config['launcher']['user']];
    $p = $_POST[$config['launcher']['pass']];
}else{
    $u = $_GET[$config['launcher']['user']];
    $p = $_GET[$config['launcher']['pass']];
}

function cp($pass, $hash, $type, $salt = null)
{
    switch ($type) {
        case 0:
            return md5($pass) == $hash;
            break;
        case 1:
            return md5(md5($pass)) == $hash;
            break;
        case 2:
            return md5($pass . $salt) == $hash;
            break;
        case 3:
            return md5(md5($pass) . $salt) == $hash;
            break;
        case 4:
            return password_verify($pass, $hash);
            break;
    }
}

switch ($config['launcher']['type']) {
    case 0:
        $q = $mysql->query("SELECT * FROM mcr_users WHERE login = :u OR email = :u", ['u' => $u]);
        $res = cp($p, $q['password'], $config['password']['webmcr'], $q['salt']);
        break;
    case 1:
        $q = $mysql->query("SELECT * FROM dle_users WHERE name = :u OR email = :u", ['u' => $u]);
        $res = cp($p, $q['password'], $config['password']['dle'], $q[$config['table']['salt']]);
        break;
    case 2:
        $q = $mysql->query("SELECT * FROM " . $config['table']['name'] . " WHERE " . $config['table']['username'] . " = :u  OR " . $config['table']['email'] . " = :u", ['u' => $u]);
        $res = cp($p, $q[$config['table']['password']], $config['password']['other_hash'], $q[$config['table']['salt']]);
        break;
}
if ($res == false) {
    err('pas');
}
if ($config['additions']['type_ban'] == 1 && $q[$config['table']['ban']] ==  1 && $q[$config['table']['bt']] > time()) {
    ban($q[$config['table']['br']]);
} else if ($config['additions']['type_ban'] == 2) {
    $qb = $mysql->query("SELECT * FROM " . $config['ban']['name'] . " WHERE " . $config['ban']['username'] . " = :u", ['u' => $u]);
    if ($qb != "" && $qb[$config['ban']['time']] > time()) {
        ban($qb[$config['ban']['reason']]);
    }
}

if($config['additions']['type_whi'] == 1){
    if($q[$config['table']['wh']] == 0)
        err("wht");
}else if($config['additions']['type_whi'] == 2){
    $qw = $mysql->query("SELECT * FROM " . $config['wht']['name'] . " WHERE " . $config['wht']['username'] . " = :u", ['u' => $u]);
    if(!$qw)
        err("wht");
}
function oke($user){
    if($GLOBALS['config']['type'] == 1){
        die(json_encode(['user' => $user]));
    }else{
        die("OK:".$user);
    }
}
switch ($config['launcher']['type']) {
    case 0:
        if($res === true)
            oke($q['login']);
        break;
    case 1:
        if($res === true)
            oke($q['login']);

        break;
    case 2:
        if($res === true)
            oke($q[$config['table']['username']]);
        break;
}
