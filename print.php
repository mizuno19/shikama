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
    <form>
    <p>セイ<input type="text" name="surname"></p>
    <p>メイ<input type="text" name="name"></p>
    <p>姓　名<input type="text" name=""></p>
    <p>来店日時<input type="text" name="prefe"></p>
    <p>メニュー<input type="text" name="prefe"></p>
    <p>好み・苦手なもの<input type="text" name="prefe"></p>
    <input type="submit" value="印刷">
</form>   
</body>
</html>