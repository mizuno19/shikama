<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// IDの取得
if (isset($_POST['ID']) && !empty($_POST['ID'])) {
    $where_in = '';
    foreach ($_POST['ID'] as $id) {
        $where_in .= "'$id',";
    }
    $where_in = substr($where_in, 0, -1);   // 最後に付く「,」を削除
}

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

<?php
    // 削除確認画面から飛んできた時の処理
    if (isset($_POST['DELFIX'])) {    
        // 削除処理
        $csql = "DELETE FROM 顧客 WHERE 顧客ID IN (${where_in})";
        $rsql = "DELETE FROM 来店記録 WHERE 顧客ID IN (${where_in})";
        $tsql = "DELETE FROM 電話番号 WHERE 顧客ID IN (${where_in})";
        $bsql = "DELETE FROM 生年月日 WHERE 顧客ID IN (${where_in})";
    // 顧客、電話番号、生年月日のそれぞれのテーブルから
    // 削除する顧客の情報が消せるクエリになっているか確認
    // クエリの形が確認できるまでは実行しない
        $tres = execute($dbo, $tsql);
        $bres = execute($dbo, $bsql);
        $rres = execute($dbo, $rsql);
        $cres = execute($dbo, $csql);
        if (!$cres || !$tres || !$bres || !$rres) {
           $mess = '削除に失敗しました';
        }else{
            $mess = '削除しました';
        }
        echo $mess;

    // 顧客一覧から削除ボタンが押された時の処理
    } else if (isset($_POST['DELETE']) && isset($_POST['ID']) && !empty($_POST['ID'])) {
        // 削除確認
?>
<div id="ver">削除しますか？</div>
<?php
    // 確認表示に使うため、条件に合う顧客の情報を取得
    $csql = "SELECT 顧客ID, 姓, 名, セイ, メイ FROM 顧客 WHERE 顧客ID IN (${where_in}) ORDER BY 顧客ID";
    $cres = execute($dbo, $csql);

    // クエリの結果で処理を分ける
    if (!$cres) {
        echo "<p>顧客テーブルからデータを読み込めませんでした。</p>";
    } else {
        // 結果が空でなければデータを配列で取得
        $db_data = $cres->fetchAll(PDO::FETCH_ASSOC);
    }
?>
<form action="" method="POST">
<?php
    // データベースから取得したデータの表示
    for ($i = 0; $i < count($db_data); $i++) {
        $kana = $db_data[$i]['セイ'] . "　" . $db_data[$i]['メイ'];
        $name = $db_data[$i]['姓'] . "　" . $db_data[$i]['名'];
        $id = $db_data[$i]['顧客ID'];
        $tel = '登録なし';
        // 顧客の電話番号を1件だけ取得
        $tsql = "SELECT 電話番号 FROM 電話番号 WHERE 顧客ID = '" . $db_data[$i]['顧客ID'] . "' LIMIT 1";
        $tres = execute($dbo, $tsql);
        // クエリが成功したかチェック
        if (!$tres) {
            echo "<p>電話番号テーブルからデータを読み込めませんでした。</p>";
        } else {
            // クエリが成功していたら電話番号を取得する
            $db_tel = $tres->fetchAll(PDO::FETCH_ASSOC);
            // 登録されていない場合があるのでチェック
            if (!empty($db_tel[0]['電話番号'])) {
                $tel = $db_tel[0]['電話番号'];
            }
        }
?>
<input type="hidden" name="ID[]" value="<?=$id?>">
<div class="del_client">
    <p class="kana"><?php echo $kana?>　サマ</p>
    <p class="name"><?php echo $name ?>　様</p>
    <p class="tel">連絡先: <?php echo $tel ?></p>
</div>
<?php
    }
?>
<div class="submit_btn">
    <input type="submit" name="DELFIX" value="確定">
</div>
</form>

<?php
    } else {
        // 削除する顧客が選択されていない場合は顧客一覧へ
        header('Location: ./');
    }
?>


</section>
</body>
</html>
<?php
    $dbo = null;
?>
