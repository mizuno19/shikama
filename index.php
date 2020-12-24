<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// テーブル内の全データ数を取得
$max = execute($dbo, "SELECT COUNT(*) AS データ数 FROM 顧客")->fetch(PDO::FETCH_ASSOC)["データ数"];
$n = 0;     // スキップ数
$m = 5;    // 取得数

if (isset($_GET['NEXT']) && is_numeric($_GET['NEXT'])) {
    $n = $_GET['NEXT'];
} else {
    $n = 0;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/base.css" type="text/css">
    <link rel="stylesheet" href="css/index.css" type="text/css">
    <title>顧客一覧</title>
</head>
<body>
<div id="content">
<header>
    <h1>顧客一覧</h1>
    <nav><ul>
        <li><a href="./"><button>全件表示</button></a></li>
        <li><a href="register.php"><button>新規登録</button></a></li>
    </ul></nav>
</header>
<div id="search_box">
    <form id="search" action="" method="GET">
        <input type="text" name="SEARCH" value="">
        <input type="submit" name="SEARCH_SEND" value="検索">
    </form>
</div>
<div id="next">
    <p>
<?php
    for ($i = 0; $i < ($max / $m); $i++) {
?>
    <a href="?NEXT=<?= $i * $m ?>"><?= ($i + 1) ?></a>
<?php
    }
?>
    </p>
</div>
<section id="main">
    <div id="client_list">
<?php
$where = '';
// 検索ボタン押下のチェック
if (isset($_GET['SEARCH_SEND']) && !empty($_GET['SEARCH'])) {
    // 検索ワードの取得
    $where = get_search_where_sql($_GET['SEARCH']);
}
$order = "ORDER BY 顧客ID DESC";
$limit = "LIMIT ${m} OFFSET ${n}";
// $limit = "OFFSET ${n} ROWS FETCH NEXT ${m} ROWS ONLY";   // MySQL使えないっぽい

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
        <div class="visit">
            <a href="./visit.php?ID=<?= $id ?>"><button>来店情報登録</button></a>
        </div>
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
