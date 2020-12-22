<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// HTMLヘッダ情報とヘッダ部分の表示
?>
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

<?php
$n = 0;     // スキップ数
$m = 20;    // 取得数
$where = '';

// 検索ボタン押下のチェック
if (isset($_GET['SEARCH_SEND'])) {
    // 検索ワードの有無をチェック
    if (isset($_GET['SEARCH']) && !empty($_GET['SEARCH'])) {
        // 検索ワードの取得
        $search_word = $_GET['SEARCH'];
        $word = $search_word;

        $or = " OR ";
        $where = "WHERE ";

        // 検索ワードが氏名の各列に含まれているか曖昧検索のための準備
        $where .= "姓 LIKE '%" . $word . "%' ";
        $where .= $or;
        $where .= "名 LIKE '%" . $word . "%' ";
        $where .= $or;
        $where .= "セイ LIKE '%" . $word . "%' ";
        $where .= $or;
        $where .= "メイ LIKE '%" . $word . "%'";
    }
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
    var_dump($db_data);
    if (empty($db_data)) {
        // 空だった場合
        echo "<p>登録されているデータはありません。</p>";
    } else {
        // データ出力
        echo '<div class="client_list">';
        $cnt = $n;
        foreach ($db_data as $row) {
            $id = $row['顧客ID'];
            $name = $row['姓'] . "　" . $row['名'];
            $kana = $row['セイ'] . "　" . $row['メイ'];
?>
            <div class="client"><label id="<?= $cnt ?>">
            <div class="id"><input type="checkbox" name="id[]" value="<?= $id ?>"></div>
            <div class="no"><?= ($cnt + 1) ?></div>
            <div class="name_box">
            <div class="kana"><?= $kana ?></div>
            <div class="name"><a href="print.php?ID=<?= $id ?>"><?= $name ?></a></div>
            </div>
            </label>
            <div class="visit"><form action="visit.php" method="GET">
            <input type="hidden" name="ID" value="<?= ${id} ?>">
            <input type="submit" name="VISIT" value="来店情報登録">
            </form></div>
            </div>
<?php
            $cnt++;
        }
        echo '</div>';
    }
}
?>

</body>
</html>

<?php
$dbo = null;
?>
