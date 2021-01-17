<?php
require_once 'config.php';
require_once 'lib/dblib.php';

$message = '';

$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/base.css" type="text/css">
    <link rel="stylesheet" href="css/visit.css" type="text/css">
    <title>来店情報登録</title>
</head>
<body>
<div id="content">
<header>
    <h1>来店情報登録</h1>
    <nav><ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul></nav>
</header>
<section id="main">
<?php
// 顧客IDのチェック
if (isset($_GET['ID'])) {
    $id = $_GET['ID'];
} else {
    // 顧客IDが送られてきてないのはおかしいのでトップへ
    header('Location: /');
    exit;
}

# POSTでデータが送信されていれば登録処理
if (isset($_POST['SEND'])) {
    $err_flag = false;      // エラーフラグ
    $forms = array();       // 空の配列を準備
    // 列名をキーにして連想配列を作成
    foreach ($_POST as $key => $value) {
        $forms[$key] = $value;
    }
    //確認用
    // echo "<hr>";
    // var_dump($forms);
    // echo "<hr>";
    $date = $forms["year"]."-".$forms["month"]."-".$forms["day"]." ".$forms["hour"].":".$forms["minute"].":"."00";
    // var_dump($date);
    // 同じ日時のデータが連続して入力される時は登録をスキップ(更新ボタンを押した時の処理)
    $sql = "SELECT COUNT(*) AS count FROM 来店記録 WHERE 顧客ID='" . $forms['ID'] . "' AND 日時='${date}'";
    $res = execute($dbo, $sql);
    $count = $res->fetch(PDO::FETCH_ASSOC)['count'];
    if ($count > 0) {
        $message = "重複登録のため登録をスキップしました。";
    } else {
        insert_visit($dbo, $forms["ID"], $date, $forms["NUMBER"], $forms["RELATION"], $forms["EATS"]);
        $message = "登録完了しました。";
    }
}

// 選択された顧客情報を取得
$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ, 備考 FROM 顧客 WHERE 顧客ID = :id";
$stmt = $dbo->prepare($sql);
$stmt->bindValue(':id', $id);
$stmt->execute();
$res = $stmt->fetch(PDO::FETCH_ASSOC);
$year = "";
$month = "";
$day = "";
$hour = "";
$minute = "";

if (!$res) {
    echo "<p>データが取得できていません。</p>";
} else {
    if (!isset($_POST['SEND'])) {
        $date = date('Y-m-d H:i:s');
        echo '<div class="name"><p>' . $res['セイ'] . '　' . $res['メイ'] . '</p>';
        echo '<p>' . $res['姓'] . '　' . $res['名'] . '様</p></div>';
        echo '<form action="" method="POST">';
        echo "<input type=\"hidden\" name=\"ID\" value=\"${id}\">";
        echo "<input type=\"button\" value=\"現在時刻取得\" onClick=\"getNow();\">".'<br>';
        echo "<p>来店日時:";
        echo "<input type=\"text\" name=\"year\" value=\"$year\" id=\"year\" size=\"3\">".'年';
        echo "<input type=\"text\" name=\"month\" value=\"$month\" id=\"month\" size=\"3\">".'月';
        echo "<input type=\"text\" name=\"day\" value=\"$day\" id=\"day\" size=\"3\">".'日';
        echo "<input type=\"text\" name=\"hour\" value=\"$hour\" id=\"hour\" size=\"3\">".'時';
        echo "<input type=\"text\" name=\"minute\" value=\"$minute\" id=\"minute\" size=\"3\">".'分';
        echo "</p>";
        echo "<p>人数：";
        echo "<input type=\"text\" name=\"NUMBER\" value=\"\" size=\"3\" maxlength=\"2\">"."人";
        echo "</p>";
        echo "<p>続柄:";
        echo "<input type=\"text\" name=\"RELATION\" value=\"\" size=\"10\" maxlength=\"20\">";
        echo "</p>";
        echo "<p>食べたもの:";
        echo "<textarea rows=\"5\" cols=\"50\" name=\"EATS\"></textarea>";
        echo "</p>";
        echo '<p>';
        echo '<input type="submit" name="SEND" value="登録">';
        echo '</p>';
        echo '</form>';
    } else {
        if (!empty($message)) {
            echo "<p class=\"message\">${message}</p>";
        }
        echo '<a href="./">顧客一覧へ</a>';
    }
}
$dbo = null;
?>
</selection>
<script>
var now = new Date();
function getNow() {
    var Year = now.getFullYear();
    document.getElementById("year").value=Year;
    var Month = now.getMonth()+1;
    document.getElementById("month").value=Month;
    var Date = now.getDate();
    document.getElementById("day").value=Date;
    var Hour = now.getHours();
    document.getElementById("hour").value=Hour;
    var Min = now.getMinutes();
    document.getElementById("minute").value=Min;
}
</script>
</body>
</html>
