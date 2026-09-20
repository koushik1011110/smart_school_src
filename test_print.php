<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once('application/config/database.php');

$db_conf = $db['default'];
$mysqli = new mysqli($db_conf['hostname'], $db_conf['username'], $db_conf['password'], $db_conf['database']);

if ($mysqli->connect_error) {
    die("Connect failed: " . $mysqli->connect_error);
}

$h = '$2y$10$W/sBAkGhku2PhKqJA.xoXutNOoQnj/2/JA3Dmo7pugAJUiVlfltf.';
foreach(['admin','password','123456','admin123','admin@123','superadmin','school','SVN@1998C#','kkwebmartsolution@gmail.com','9000'] as $p){
    if(password_verify($p, $h)) echo 'Matched: ' . $p . PHP_EOL;
}


