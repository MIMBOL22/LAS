<?php
/***********************************************************
 *
 *  Скрипт авторизации by MIMBOL
 *  Тип авторизации: JSON,Request
 *  Лаунчер: Sashok KeeperJerry >1.5 | Gravit Launcher < 4.0
 *  Версия: 1.3
 *  Поддержка сайтов: WebMCR , DLE , Самопис
 *  Хэши: BCrypt , MD5
 *
 *************************************************************/
 
error_reporting(E_ALL);

ini_set('display_errors', ($config['teh']  && $_GET['debug'] == "1") ? "1" : "0");
ini_set('display_startup_errors',($config['teh']  && $_GET['debug'] == "1") ? "1" : "0");

include 'config.php';
include 'function.php';
include 'mysql.php';

error_verify('teh', $config['teh']  && $_GET['debug'] != "1");
error_verify('adm', $_GET['key'] != $config['launcher']['key']);

$user = $GLOBALS[$config['type'] ? "_POST" : "_GET"][$config['launcher']['user']];
$pass = $GLOBALS[$config['type'] ? "_POST" : "_GET"][$config['launcher']['pass']];

$mysql = new mysqlo($config['database']);

$tys = $config['password']['system']; // TypeSystem
$tas = $tableHashs[$tys]['tables'];// TableSystem

$q = $mysql->query("SELECT * FROM {$tas[0]} WHERE {$tas[1]} = :u OR {$tas[4]} = :u", ['u' => $user]);
$res = verify($pass, $q[$tas[3]], getHash($config['password']['system'],$config['password']['add'][$tys]), $q[$tas[2]]);
error_verify('pas', !$res);

checkWhitelist($mysql,$q,$config);
checkBan($mysql,$q,$config);

if ($res === true  && $_GET['debug'] != "1")
    ok($q[$tas[1]]);

