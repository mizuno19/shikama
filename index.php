<?php
require_once 'config.php';
require_once 'lib/dblib.php';

$view_html = "regist.html";

$db_dsn = [
    'dsn' => "mysql:dbname=" . Config::DB_DBNAME
                . ";host=" . Config::DB_HOST
                . ";port=" . Config::DB_PORT
                . ";charset=" . Config::DB_CHARSET,
    'user' => Config::DB_USER,
    'pass' => Config::DB_PASSWD,
    'opt' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,    // 静的プレースホルダを使う
    ]
];

$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');


if (isset($_GET['SEND'])) {
    $sql = "SELECT * FROM client;";
    $res = $dbo->query($sql);
    var_dump($res);
    echo "クエリ実行<br>";
} else {
    // 表示用データの読込
    $html = get_html($view_html);
    if (empty($html)) die('Error: 指定されたファイルが見つかりません');
    echo $html;
}

$dbo = null;



function get_html($file) {
    $ret = null;
    $fp = fopen($file, 'r');
    while ($line = fgets($fp)) {
        $ret .= $line;
    }
    fclose($fp);

    return $ret;
}

?>
