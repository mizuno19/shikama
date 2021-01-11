<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/base.css" type="text/css">
    <link rel="stylesheet" href="css/delete.css" type="text/css">
    <title>顧客情報削除</title>
</head>
<body>
<div id="content">
<header>
    <h1>顧客情報削除</h1>
    <nav><ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul></nav>
</header>
<section id="main">

</section>
</div>
</body>
</html>
<?php
    $dbo = null;
?>
