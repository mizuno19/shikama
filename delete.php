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
<form>
<input type="hidden" name="ID[]" value="<?=$id?>">
<div id="content">
<header>
    <h1>顧客情報削除</h1>
    <nav><ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul></nav>
</header>
<section id="main">
<div id=ver>削除しますか？</div>
<?php
$id = $_POST['ID'][0];
$csql = "SELECT 顧客ID, 姓, 名, セイ, メイ FROM 顧客 where '$id' = 顧客ID";
$tsql = "SELECT 顧客ID,電話番号 FROM 電話番号 where '$id' = 顧客ID limit 1";
$cres = execute($dbo, $csql);
$tres = execute($dbo, $tsql);
var_dump($cres);
var_dump($tres);
if (empty($id)) {
    echo "<p>テーブルからデータを読み込めませんでした。</p>";
} else {
    // 結果が空でなければデータを配列で取得
    $db_data = $cres->fetchAll(PDO::FETCH_ASSOC);
var_dump($db_data);
foreach ($db_data as $row) {
    $name = $row['姓'] . "　" . $row['名'];
    $kana = $row['セイ'] . "　" . $row['メイ'];
    $db_data = $tres->fetchAll(PDO::FETCH_ASSOC);
var_dump($db_data);
foreach ($db_data as $row) {
    $tel = $row['電話番号'];
}
}
}
?>
<div id=name>
<p><?php echo $db_data[0]["セイ"]." ".$db_data[0]["メイ"] ?>　サマ</p>
<p><?php echo $db_data[0]["姓"]." ".$db_data[0]["名"] ?>　様</p></div>
<div id=kana></div>
<div id=tel></div>
<input type="submit" value="確定">
</section>
</div>
</form>
</body>
</html>
<?php
    $dbo = null;
?>
