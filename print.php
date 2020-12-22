<?php
require_once 'config.php';
require_once 'lib/dblib.php';

$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" href="css/base.css" type="text/css">
    <link rel="stylesheet" href="css/print.css" type="text/css">
</head>
<body>
<?php 
$n = 0;     // スキップ数
$m = 0;    // 取得数
$where = '';
$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ FROM 顧客 ${where} ${order} ${limit}";
$res = execute($dbo, $sql); ?>
    <form>
    <p>セイ メイ:</p>
    <p>姓 名:</p>
    <p>来店日時:</p>
    <p>メニュー:</p>
    <p>好み・苦手なもの:</p>
    <input type="submit" value="印刷">
</form>   
</body>
</html>