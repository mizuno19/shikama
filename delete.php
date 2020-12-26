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
<div id="client_list">
<?php
$where = '';
$order = "ORDER BY 顧客ID DESC";
$limit = "LIMIT ${m} OFFSET ${n}";

// SQLの生成
$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ FROM 顧客 ${where} ${order} ${limit}";
// クエリの発行
$res = execute($dbo, $sql);

// クエリ実行結果のチェック
if (empty($res)) {
    // 結果が空だった場合
    echo "<p>テーブルからデータを読み込めませんでした。</p>";
} else {
    // 結果が空でなければデータを配列で取得
    $db_data = $res->fetchAll(PDO::FETCH_ASSOC);
    // var_dump($db_data);
    if (empty($db_data)) {
        // 空だった場合
        echo "<p>登録されているデータはありません。</p>";
    } else {
        // データ出力
        $cnt = $n;
        foreach ($db_data as $row) {
            $id = $row['顧客ID'];
            $name = $row['姓'] . "　" . $row['名'];
            $kana = $row['セイ'] . "　" . $row['メイ'];
?>
    <div class="client">
        <label id="<?= $cnt ?>">
        <div class="id"><input type="checkbox" name="id[]" value="<?= $id ?>"></div>
        <div class="no"><span><?= ($cnt + 1) ?></span></div>
        <div class="name_box">
            <div class="kana"><?= $kana ?></div>
            <div class="name"><a href="print.php?ID=<?= $id ?>"><?= $name ?></a></div>
        </div>
        </label>
    </div>
<?php
            $cnt++;
        }
    }
}
?>
    </div>
</section>
</div>
</body>
</html>
<?php
    $dbo = null;
?>
