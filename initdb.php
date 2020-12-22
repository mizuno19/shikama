<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
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


echo "<hr><p>テーブル削除</p><hr>";
drop_table($dbo, "電話番号");
drop_table($dbo, "連絡先区分");
drop_table($dbo, "生年月日");
drop_table($dbo, "来店記録");
drop_table($dbo, "顧客");


echo "<hr><p>テーブル作成</p><hr>";
$sql = "create table 顧客(
    顧客ID char(7) not null,
    姓 varchar(100),
    名 varchar(100),
    セイ varchar(100),
    メイ varchar(100),
    備考 text,
    primary key(顧客ID));";
execute($dbo, $sql, true);

$sql = "create table 連絡先区分(
    区分ID char(1) not null,
    区分名 varchar(30) not null,
    primary key(区分ID));";
execute($dbo, $sql, true);

$sql = "create table 電話番号(
    顧客ID char(7) not null,
    区分ID char(1) not null,
    電話番号 varchar(11) not null,
    primary key(顧客ID,区分ID),
    foreign key (顧客ID) references 顧客(顧客ID),
    foreign key (区分ID) references 連絡先区分(区分ID));";
execute($dbo, $sql, true);

$sql = "create table 生年月日(
    顧客ID char(7) not null,
    登録ID int not null,
    生年月日 date ,
    続柄 varchar(10),
    primary key(顧客ID,登録ID),
    foreign key (顧客ID) references 顧客(顧客ID));";
execute($dbo, $sql, true);

$sql = "create table 来店記録(
    顧客ID char(7) not null,
    日時 timestamp not null,
    人数 int,
    続柄 varchar(10),
    メニュー text,
    primary key(顧客ID));";
execute($dbo, $sql, true);


echo "<hr><p>初期データ投入</p><hr>";
$sql = "insert into 連絡先区分 values
      ('1','自宅')
    , ('2','会社')
    , ('3','本人')
    , ('4','その他')";
execute($dbo, $sql, true);


echo "<hr><p>サンプルデータ</p><hr>";
$sql = "insert into 顧客 values
    ('2011121', '出江田', '入太郎', 'デエタ', 'イレタロウ', '好きなもの：肉　食べられないもの：牛');";
execute($dbo, $sql, true);
$sql = "insert into 電話番号 values
      ('2011121', '1', '0123456789')
    , ('2011121', '3', '08011112222');";
execute($dbo, $sql, true);
$sql = "insert into 生年月日 values
      ('2011121', 1, '1978-01-01', '本人')
    , ('2011121', 2, '1980-04-28', '妻');";
execute($dbo, $sql, true);

$sql = "insert into 顧客 values
    ('2011131', '船橋', '太郎', 'フナバシ', 'タロウ', '好きなもの：おかめ納豆　嫌いなもの：ピーマン');";
execute($dbo, $sql, true);
$sql = "insert into 電話番号 values
    ('2011131', '1', '08022223333');";
execute($dbo, $sql, true);
$sql = "insert into 生年月日 values
      ('2011131', 1, '1978-01-01', '本人');";
execute($dbo, $sql, true);

$sql = "insert into 顧客 values
    ('2011151', '津田', '沼夫', 'ツダ', 'ヌマオ', '好きなもの：ナス');";
execute($dbo, $sql, true);
execute($dbo, $sql, true);
$sql = "insert into 生年月日 values
        ('2011151', 1, '1978-01-01', '本人')
      , ('2011151', 2, '1980-07-01', '友人')
      , ('2011151', 3, '1986-03-05', '友人')
      , ('2011151', 4, '1986-10-05', '友人');";
execute($dbo, $sql, true);

$sql = "insert into 顧客 values
    ('2011171', '稲毛', '浜', 'イナゲ', 'ハマ', 'アレルギー：乳製品');";
execute($dbo, $sql, true);
$sql = "insert into 電話番号 values
      ('2011171', '1', '1234506789')
    , ('2011171', '2', '9876543210')
    , ('2011171', '3', '08033334444');";
execute($dbo, $sql, true);



echo <<<"EOH"
</body>
</html>
EOH;

?>
