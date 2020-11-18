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
<h1>顧客情報一覧</h1>
<hr>
<div id="header_box">
    <form action="" method="GET">
    <div id="search">
        <input type="text" name="SEARCH" value="">
        <input type="submit" name="SEARCH_SEND" value="検索">
        <div id="register">
            <a href="register.php">新規顧客登録</a>
        </div>
    </div>
    </form>
</div>
<hr>
EOH;

$n = 0;     // スキップ数
$m = 20;    // 取得数
if (isset($_GET['SEARCH_SEND'])) {
    if (isset($_GET['SEARCH']) && !empty($_GET['SEARCH'])) {
        $search_word = $_GET['SEARCH'];
        str_replace($search_word, "||")

        $where = "WHERE ";
        $where .= "姓 LIKE '%" . $word . "%' ";
        $where .= "名 LIKE '%" . $word . "%' ";
        $where .= "セイ LIKE '%" . $word . "%' ";
        $where .= "メイ LIKE '%" . $word . "%'";
    }
}

$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ FROM 顧客 ${where} LIMIT ${m}";
$res = execute($dbo, $sql);


if (empty($res)) {
    echo "<p>テーブルからデータを読み込めませんでした。</p>";
} else {
    $db_data = $res->fetchAll(PDO::FETCH_ASSOC);
    if (empty($db_data)) {
        echo "<p>登録されているデータはありません。</p>";
    } else {
        echo '<div class="client_list">';
        $cnt = $n;
        foreach ($db_data as $row) {
            $id = $row['顧客ID'];
            $name = $row['姓'] . "　" . $row['名'];
            $kana = $row['セイ'] . "　" . $row['メイ'];
            echo "<div class=\"client\"><label id=\"${cnt}\">";
            echo "<div class=\"id\"><input type=\"checkbox\" name=\"id[]\" value=\"${id}\"></div>";
            echo "<div class=\"no\">${cnt}</div>";
            echo '<div class="name_box">';
            echo "<div class=\"kana\">${kana}</div>";
            echo "<div class=\"name\">${name}</div>";
            echo '</div>';
            echo '</label>';
            echo '<div class="visit"><form action="visit.php" method="GET">';
            echo "<input type=\"hidden\" name=\"ID\" value=\"${id}\">";
            echo '<input type="submit" name="VISIT" value="来店情報登録">';
            echo '</form></div>';
            echo '</div>';
            $cnt++;
        }
        echo '</div>';
    }
}

echo <<<"EOH"
</body>
</html>
EOH;

$dbo = null;

?>
