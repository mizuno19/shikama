<?php
require_once 'config.php';
require_once 'lib/dblib.php';


$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

echo <<<"EOH"
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/base.css" type="text/css">
    <link rel="stylesheet" href="css/index.css" type="text/css">
</head>
<body>
<h1>来店情報登録</h1>
<hr>
EOH;

// 顧客IDのチェック
if (isset($_GET['ID'])) {
    $id = $_GET['ID'];
} else {
    // 顧客IDが送られてきてないのはおかしいのでトップへ
    header('Location: /');
    exit;
}

// 選択された顧客情報を取得
$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ, 備考 FROM 顧客 WHERE 顧客ID = :id";
$stmt = $dbo->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$res) {
    echo "<p>データが取得できていません。</p>";
} else {
    if (!isset($_POST['SEND'])) {
        $date = date('Y-m-d H:i:s');
        echo '<p>' . $res['セイ'] . '　' . $res['メイ'] . '</p>';
        echo '<p>' . $res['姓'] . '　' . $res['名'] . '様</p>';
        echo '<form action="" method="POST">';
        echo "<input type=\"hidden\" name=\"ID\" value=\"${id}\">";
        echo "<p>来店日時：";
        echo "<input type=\"text\" name=\"DATE\" value=\"${date}\">";
        echo "</p>";
        echo "<p>人数：";
        echo "<input type=\"text\" name=\"NUMBER\" value=\"\" size=\"3\" maxlength=\"2\">";
        echo "</p>";
        echo "<p>続柄(?):";
        echo "<input type=\"text\" name=\"RELATION\" value=\"\" size=\"10\" maxlength=\"20\">";
        echo "</p>";
        echo '<p>';
        echo '<input type="submit" name="SEND" value="登録">';
        echo '</p>';
        echo '</form>';
    } else {
        
        echo "登録処理<br>";
        echo '<a href="/">トップへ</a>';
    }
}


echo <<<"EOH"
</body>
</html>
EOH;

$dbo = null;

?>
