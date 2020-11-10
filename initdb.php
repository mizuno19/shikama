<?php
require_once 'config.php';
require_once 'lib/dblib.php';

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

echo <<<"EOH"
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>DB初期化</title>
    <style type="text/css">
    p {
        font-size: 0.9em;
        margin: 0;
    }
    </style>
</head>
<body>
EOH;


// テーブルを一度削除
drop_table($dbo, "電話番号");
drop_table($dbo, "連絡先区分");
drop_table($dbo, "生年月日");
drop_table($dbo, "来店記録");
drop_table($dbo, "顧客");

echo "<hr>";
echo "<p>----- テーブル作成 ------</p>";

// テーブルの作成確認
$sql = "create table 顧客(
    顧客ID char(7) not null,
    姓 varchar(100),
    名 varchar(100),
    セイ varchar(100),
    メイ varchar(100),
    備考 text,
    primary key(顧客ID));";
create_table($dbo, $sql);

$sql = "create table 連絡先区分(
    区分ID char(1) not null,
    区分名 varchar(30) not null,
    primary key(区分ID));";
create_table($dbo, $sql);

$sql = "create table 電話番号(
    顧客ID char(7) not null,
    電話番号 varchar(11) not null,
    区分ID char(1) not null,
    primary key(顧客ID,区分ID),
    foreign key (顧客ID) references 顧客(顧客ID),
    foreign key (区分ID) references 連絡先区分(区分ID));";
create_table($dbo, $sql);

$sql = "create table 生年月日(
    顧客ID char(7) not null,
    登録ID int not null,
    生年月日 date ,
    続柄 varchar(10),
    primary key(顧客ID,登録ID),
    foreign key (顧客ID) references 顧客(顧客ID));";
create_table($dbo, $sql);

$sql = "create table 来店記録(
    顧客ID char(7) not null,
    日時 timestamp not null,
    人数 int,
    続柄 varchar(10),
    メニュー text,
    primary key(顧客ID));";
create_table($dbo, $sql);

echo <<<"EOH"
</body>
</html>
EOH;


function drop_table($dbo, $table) {
    $sql = "DROP TABLE ${table};";
    try {
        $res = $dbo->query($sql);
        echo "<p>${table} テーブルを削除しました。</p>";
    } catch (Exception $e) {
        echo "<p>${table} テーブルの削除に失敗しました。<br>";
        echo "&nbsp;&nbsp;(" . $e->getMessage() . ")</p>";
    }
}

function create_table($dbo, $sql) {
    try {
        $dbo->query($sql);
        echo "<p>【 SQL実行成功 】</p>";
        echo "<p>[ ${sql} ]</p>";
    } catch (Exception $e) {
        echo "<p>${sql}の実行に失敗しました。<br>";
        echo "&nbsp;&nbsp;(" . $e->getMessage() . ")</p>";
    }
}

?>
