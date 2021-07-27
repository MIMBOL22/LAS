<?php
function error_verify(string $mess, bool $verify)
{
    if($verify)
            die($GLOBALS['config']['type'] ? json_encode(['error' => $GLOBALS['config']['mess'][$mess]]) : $GLOBALS['config']['mess'][$mess] );
}

function ban($reas)
{
    if ($GLOBALS['config']['additions']['er'] == 1)
        $res = " по причине " . $reas;
    die( $GLOBALS['config']['type'] ? json_encode(['error' => $GLOBALS['config']['mess']['ban'] . $res]) : $GLOBALS['config']['mess']['ban'] . $res);
}

function ok($user)
{
        die( $GLOBALS['config']['type'] ? json_encode(['user' =>$user]) : "OK:" .$user );
}

function verify($pass, $hash, $type, $salt = null)
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

function getHash($system,$attr){
    return $GLOBALS['tableHashs'][$system]['hash'][$attr];
}

function checkWhitelist($mysql,$q,$config){
    if ($config['additions']['type_whi'] == 0) return false;
    $qw = $mysql->query("SELECT * FROM {$config['wht']['name']} WHERE {$config['wht']['username']} = :u", ['u' => $q[ $GLOBALS['tab'][$config['password']['system']]['tables'][1]]]);
    error_verify( "wht", $config['additions']['type_whi'] == 1 ? !$q[$config['table']['wh']] : !$qw);
}

function checkBan($mysql,$q,$config)
{
    if ($config['additions']['type_ban'] == 1 && $q[$config['table']['ban']] == 1 && $q[$config['table']['bt']] > time()) {
        ban($q[$config['table']['br']]);
    } else if ($config['additions']['type_ban'] == 2) {
        $qb = $mysql->query("SELECT * FROM " . $config['ban']['name'] . " WHERE " . $config['ban']['username'] . " = :u", ['u' => $user]);
        if ($qb != "" && $qb[$config['ban']['time']] > time()) {
            ban($qb[$config['ban']['reason']]);
        }
    }
}
