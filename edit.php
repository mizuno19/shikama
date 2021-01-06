<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// 更新処理
if (isset($_POST['UPDATE'])) {
    $forms = array();       // 空の配列を準備
    // 列名をキーにして連想配列を作成
    foreach ($_POST as $key => $value) {
        $forms[$key] = $value;
    }

    var_dump($forms);

    $errnum = 0;
    $kokyaku_sql = "UPDATE 顧客 SET 姓=:sei, 名=:mei, セイ=:k_sei, メイ=:k_mei, 備考=:like WHERE 顧客ID=:id";
    $kokyaku = $dbo->prepare($kokyaku_sql);
    $kokyaku->bindParam(":id", $forms['ID']);
    $kokyaku->bindParam(":sei", $forms['SEI']);
    $kokyaku->bindParam(":mei", $forms['MEI']);
    $kokyaku->bindParam(":k_sei", $forms['KANASEI']);
    $kokyaku->bindParam(":k_mei", $forms['KANAMEI']);
    $kokyaku->bindParam(":like", $forms['LIKE']);
    $res = $kokyaku->execute();
    if (!$res) $errnum++;

    // 電話番号が入っていない場合はその項目を削除する
    $phone_sql = "UPDATE 電話番号 SET 区分ID=:phoneclass,電話番号=:phone WHERE 顧客ID=:id AND 電話番号=:dphone";
    $phone = $dbo->prepare($phone_sql);
    $phone_new_sql = "INSERT INTO 電話番号 VALUES (:id, :phoneclass, :phone)";
    $phone_new = $dbo->prepare($phone_new_sql);
    $phone_del_sql = "DELETE FROM 電話番号 WHERE 顧客ID=:id AND 電話番号=:dphone";
    $phone_del = $dbo->prepare($phone_del_sql);

    $del_phones = array();
    $i = 0;
    foreach ($forms['PHONE'] as $tel) {
        if (empty($tel)) {
            $phone_del->bindParam(":id", $forms['ID']);
            $phone_del->bindParam(":dphone", $forms['DPHONE'][$i]);
            $res = $phone_del->execute();
        } else {
            // データが存在しているか確認
            $sql = "SELECT COUNT(*) AS count FROM 電話番号 WHERE 顧客ID=:id AND 電話番号=:phone";
            $stmt = $dbo->prepare($sql);
            $stmt->bindParam(":id", $forms['ID']);
            $stmt->bindParam(":phone", $forms['DPHONE'][$i]);
            $stmt->execute();
            $res = $stmt->fetchAll()[0]['count'];
            $stmt = null;

            // すでに登録されている番号だったかチェック
            if ($res === 0) {
                // 新規追加
                $phone_new->bindParam(":id", $forms['ID']);
                $phone_new->bindParam(":phoneclass", $forms['PHONECLASS'][$i]);
                $phone_new->bindParam(":phone", $tel);
                $res = $phone_new->execute();
            } else {
                // 更新処理
                $phone->bindParam(":id", $forms['ID']);
                $phone->bindParam(":phoneclass", $forms['PHONECLASS'][$i]);
                $phone->bindParam(":phone", $tel);
                $phone->bindParam(":dphone", $forms['DPHONE'][$i]);
                $res = $phone->execute();
            }
        }
        $i++;
    }


    // 生年月日が入っていない場合はその項目を削除する



}


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
<input value="<?= $client[0]['顧客ID'] ?>" type="hidden" name="ID">
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
    <input type="submit" name="UPDATE" value="更　新">
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
