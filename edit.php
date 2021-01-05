<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// 区分データの取得
$classes = get_phone_classes($dbo);
// JavaScriptで利用するためカンマ区切りのデータにする
$phone_classes_id = '';
$phone_classes = '';
foreach ($classes as $class) {
    $phone_classes_id .= '"' . $class['区分ID'] . '",';
    $phone_classes .= '"' . $class['区分名'] . '",';
}

// IDが送られてきていなければ顧客一覧へ遷移する
if (!isset($_GET['ID']) || empty($_GET['ID'])) {
    header('Location: ./');
    exit;
}

// 編集データの取得
$id = htmlspecialchars($_GET['ID'], ENT_QUOTES);
$sql = "SELECT 顧客ID, 姓, 名, セイ, メイ, 備考 FROM 顧客 WHERE 顧客ID = '${id}'";
$res = execute($dbo, $sql); 
$client = $res->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 区分ID, 電話番号 FROM 電話番号 WHERE 顧客ID = '${id}'";
$res = execute($dbo, $sql); 
$client_phone = $res->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 登録ID, 生年月日, 続柄 FROM 生年月日 WHERE 顧客ID = '${id}'";
$res = execute($dbo, $sql); 
$client_birth = $res->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT 日時, 人数, 続柄, メニュー FROM 来店記録 WHERE 顧客ID = '${id}'";
$res = execute($dbo, $sql); 
$client_visit = $res->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link href="css/base.css" rel="stylesheet" type="text/css">
    <link href="css/edit.css" rel="stylesheet" type="text/css">
    <title>顧客情報編集</title>
    <script>
        const phoneClassesId = [ <?= $phone_classes_id ?> ];
        const phoneClasses = [ <?= $phone_classes ?> ];
    </script>
</head>
<body>
<div id="contents">
<header>
    <h1>顧客情報編集</h1>
    <nav><ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul></nav>
</header>
<section id="main">

<form action="" method="POST" onSubmit="sendForm()">
<fieldset>
    <legend>顧客情報</legend>
    <div id="name">
        <label><span>姓：</span><input value="<?= $client[0]['姓'] ?>" type="text" name="SEI" size="8" maxlength="20"></label>
        <label><span>名：</span><input value="<?= $client[0]['名'] ?>" type="text" name="MEI" size="8" maxlength="20"></label>
    </div>
    <div id="kana">
        <label><span>セイ：</span><input value="<?= $client[0]['セイ'] ?>" type="text" name="KANASEI" size="8" maxlength="40" required="required"></label>
        <label><span>メイ：</span><input value="<?= $client[0]['メイ'] ?>" type="text" name="KANAMEI" size="8" maxlength="40" required="required"></label>
    </div>
    <div id="like">
        <label><span>備考(好みなど)：</span><textarea name="LIKE"><?= $client[0]['備考'] ?></textarea></label>
    </div>
    <div id="phone">
        <button class="btn" type="button" onClick="addChildNodes('phone_list', '', '')">＋</button>
        <span>連絡先：</span>
        <div id="phone_list"></div>
    </div>

    <div id="birthday">
        <button class="btn" type="button" onClick="addChildNodes('birthday_list', '', '')">＋</button>
        <span>生年月日：</span>
        <div id="birthday_list"></div>
    </div>

    <div id="visit">
    <?php foreach ($client_visit as $visit) { ?>
        <div id="date">
            <label><span>日時：</span><input value="<?= $visit['日時'] ?>" type="text" name="DATE" size="16" maxlength="40" required="required"></label>
        </div>
        <div id="number">
            <label><span>人数：</span><input value="<?= $visit['人数'] ?>" type="text" name="NUMBER" size="3" maxlength="3" required="required"></label>
        </div>
        <div id="relation">
            <label><span>続柄：</span><input value="<?= $visit['続柄'] ?>" type="text" name="RELATION" size="10" maxlength="50"></label>
        </div>
        <div id="menu">
            <label><span>メニュー：</span><textarea name="MENU"><?= $visit['メニュー'] ?></textarea></label>
        </div>
    <?php } ?>
    </div>
</fieldset>
<div id="send">
    <input type="submit" name="SEND" value="確　認">
</div>
</form>

<?php
$dbo = null;
?>

</section>
</div>
<script src="js/lib.js"></script>
<script>
<?php foreach ($client_phone as $phone) { ?>
    addChildNodes("phone_list", "<?= $phone['電話番号'] ?>", "<?= $phone['区分ID'] ?>");
<?php } ?>
<?php foreach ($client_birth as $birth) { ?>
    addChildNodes("birthday_list", "<?= $birth['生年月日'] ?>", "<?= $birth['続柄'] ?>");
<?php } ?>
</script>
</body>
</html>
