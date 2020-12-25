<?php
require_once 'config.php';
require_once 'lib/dblib.php';

function insert_clients($dbo, $forms) {
    try {
        // トランザクション処理開始
        $dbo->beginTransaction();

        // 顧客テーブルへ情報を追加する
        $kokyaku_sql = "INSERT INTO 顧客 VALUES (:id, :sei, :mei, :k_sei, :k_mei, :like)";
        $kokyaku = $dbo->prepare($kokyaku_sql);
        $kokyaku->bindParam(":id", $forms['ID']);
        $kokyaku->bindParam(":sei", $forms['SEI']);
        $kokyaku->bindParam(":mei", $forms['MEI']);
        $kokyaku->bindParam(":k_sei", $forms['KANASEI']);
        $kokyaku->bindParam(":k_mei", $forms['KANAMEI']);
        $kokyaku->bindParam(":like", $forms['LIKE']);
        $res = $kokyaku->execute();

        // 電話番号テーブルへ情報を追加する
        if (count($forms['PHONE']) > 0) {
            $phone_sql = "INSERT INTO 電話番号 VALUES(:id, :cid, :phone)";
            $phone = $dbo->prepare($phone_sql);
            foreach ($forms['PHONE'] as $phone_val) {
                $phone->bindParam(":id", $forms['ID']);
                $phone->bindParam(":cid", $phone_val[1]);
                $phone->bindParam(":phone", $phone_val[0]);
                $res = $phone->execute();
            }
        }

        // 生年月日テーブルへ情報を追加する
        if (count($forms['BIRTHDAY']) > 0) {
            $birth_sql = "INSERT INTO 生年月日 VALUES (:id, :bid, :birthday, :rbirthday)";
            $birth = $dbo->prepare($birth_sql);
            $i = 1;
            foreach ($forms['BIRTHDAY'] as $birthday) {
                $birth->bindParam(":id", $forms['ID']);
                $birth->bindParam(":bid", $i);
                $birth->bindParam(":birthday", $birthday[0]);
                $birth->bindParam(":rbirthday", $birthday[1]);
                $res = $birth->execute();
                $i++;
            }
        }

        // コミット
        $dbo->commit();

    } catch (PDOException $e) {
        $dbo->rollBack();
        $dbo = null;
        echo $e->getMessage();
        die();
    }
}

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
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link href="css/base.css" rel="stylesheet" type="text/css">
    <link href="css/register.css" rel="stylesheet" type="text/css">
    <title>新規登録</title>
    <script>
        const phoneClassesId = [ <?= $phone_classes_id ?> ];
        const phoneClasses = [ <?= $phone_classes ?> ];
    </script>
</head>
<body>
<div id="contents">
<header>
    <h1>新規登録</h1>
    <nav><ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul></nav>
</header>
<section id="main">

<?php
// POSTでデータが送信されていれば登録確認処理
if (isset($_POST['SEND'])) {
?>
<h2>登録内容確認</h2>
<form action="" method="POST">
<fieldset>
<legend>顧客情報</legend>
<div id="name">
    <label class="check"><span>姓：</span><span><?= $_POST['SEI'] ?></span></label>
    <label class="check"><span>名：</span><span><?= $_POST['MEI'] ?></span></label>
    <input value="<?= $_POST['SEI'] ?>" type="hidden" name="SEI">
    <input value="<?= $_POST['MEI'] ?>" type="hidden" name="MEI">
</div>
<div id="kana">
    <label class="check"><span>セイ：</span><span><?= $_POST['KANASEI'] ?></span></label>
    <label class="check"><span>メイ：</span><span><?= $_POST['KANAMEI'] ?></span></label>
    <input value="<?= $_POST['KANASEI'] ?>" type="hidden" name="KANASEI">
    <input value="<?= $_POST['KANAMEI'] ?>" type="hidden" name="KANAMEI">
</div>
<div id="like">
    <label class="check"><span>備考(好みなど)：</span><span><?= htmlspecialchars($_POST['LIKE'], ENT_QUOTES, 'UTF-8') ?></span></label>
    <input value="<?= $_POST['LIKE'] ?>" type="hidden" name="LIKE">
</div>
<div id="phone">
    <label class="check"><span>連絡先：</span>
<?php
    // 取得したデータをすべて表示
    $i = 0;
    foreach ($_POST['PHONE'] as $phone) {
?>
        <span><?= $phone ?> (<script>document.write(phoneClasses[<?= ($_POST['PHONECLASS'][$i] - 1) ?>]);</script>)</span>
        <input value="<?= $phone ?>" type="hidden" name="PHONE[]">
        <input value="<?= $_POST['PHONECLASS'][$i] ?>" type="hidden" name="PHONECLASS[]">
<?php
        $i++;
    }
?>
</div>
<div id="birthday">
    <label class="check"><span>生年月日：</span>
<?php
    // 取得したデータをすべて表示
    $i = 0;
    foreach ($_POST['BIRTHDAY'] as $birthday) {
?>
        <span><?= $birthday ?> (<?= $_POST['RBIRTHDAY'][$i] ?>)</span>
        <input value="<?= $birthday ?>" type="hidden" name="BIRTHDAY[]">
        <input value="<?= $_POST['RBIRTHDAY'][$i] ?>" type="hidden" name="RBIRTHDAY[]">
<?php
        $i++;
    }
?>
</fieldset>
<div id="send">
    <input type="submit" name="REGISTER" value="登　録">
</div>
</form>

<?php
} else if(isset($_POST['REGISTER'])) {
    // 登録処理

    $forms = array();       // 空の配列を準備
    // 列名をキーにして連想配列を作成
    foreach ($_POST as $key => $value) {
        $forms[$key] = $value;
    }

    // 顧客IDの生成
    $id = create_id($dbo);
    if (empty($id)) {
        die("<p>顧客IDの生成に失敗しました。</p>");
    }
    $forms = array('ID' => $id) + $forms;

    // 電話番号と区分を一つの配列にする
    if (!empty($forms['PHONE'])) {
        $phone = array();
        $i = 0;
        foreach ($forms['PHONE'] as $value) {
            $pclass = $forms['PHONECLASS'][$i];
            $phone += array($i => array($value, $pclass));
            $i++;
        }
    } 

    // 生成した配列をPHONEへ代入
    $forms['PHONE'] = $phone;
    // 区分は必要なくなるので削除
    unset($forms['PHONECLASS']);
    
    // 生年月日と続柄を一つの配列にする
    if (!empty($forms['BIRTHDAY'])) {
        $birthday = array();
        $i = 0;
        foreach ($forms['BIRTHDAY'] as $value) {
            $brelation = $forms['RBIRTHDAY'][$i];
            $birthday += array($i => array($value, $brelation));
            $i++;
        }
    }
    // 生成した配列をBIRTHDAYへ代入
    $forms['BIRTHDAY'] = $birthday;
    // 続柄は必要なくなるので削除
    unset($forms['RBIRTHDAY']);

    // データベースへ登録
    insert_clients($dbo, $forms);
?>
<h2>登録完了</h2>
<a href="visit.php?ID=<?= $id ?>"><button type="button">来店情報登録</button></a>
<fieldset>
<legend>登録情報</legend>
<div id="name">
    <span>氏名：</span>
    <span class="register">
        <?= $_POST['SEI'] ?>　<?= $_POST['MEI'] ?> 様
    </span>
</div>
<div id="kana">
    <span>フリガナ：</span>
    <span class="register">
        <?= $_POST['KANASEI'] ?>　<?= $_POST['KANAMEI'] ?> 様
    </span>
</div>
<div id="like">
    <span>備考(好みなど)：</span>
    <span class="register"><?= $_POST['LIKE'] ?></span>
</div>
<div id="phone">
    <span>連絡先：</span>
<?php
    // 取得したデータをすべて表示
    $i = 0;
    foreach ($_POST['PHONE'] as $phone) {
?>
        <span class="register"><?= $phone ?> (<script>document.write(phoneClasses[<?= ($_POST['PHONECLASS'][$i] - 1) ?>]);</script>)</span>
<?php
        $i++;
    }
?>
</div>
<div id="birthday">
    <span>生年月日：</span>
<?php
    // 取得したデータをすべて表示
    $i = 0;
    foreach ($_POST['BIRTHDAY'] as $birthday) {
?>
        <span class="register"><?= $birthday ?> (<?= $_POST['RBIRTHDAY'][$i] ?>)</span>
<?php
        $i++;
    }
?>
</fieldset>

<?php
} else {
    // 登録フォームの表示
?>
<form action="" method="POST" onSubmit="sendForm()">
<fieldset>
    <legend>顧客情報</legend>
    <div id="name">
        <label><span>姓：</span><input value="" type="text" name="SEI" size="8" maxlength="20"></label>
        <label><span>名：</span><input value="" type="text" name="MEI" size="8" maxlength="20"></label>
    </div>
    <div id="kana">
        <label><span>セイ：</span><input value="" type="text" name="KANASEI" size="8" maxlength="40" required="required"></label>
        <label><span>メイ：</span><input value="" type="text" name="KANAMEI" size="8" maxlength="40" required="required"></label>
    </div>
    <div id="like">
        <label><span>備考(好みなど)：</span><textarea name="LIKE">好きなもの：
嫌いなもの：</textarea></label>
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
</fieldset>
<div id="send">
    <input type="submit" name="SEND" value="確　認">
</div>
</form>

<?php
}
$dbo = null;
?>

</section>
</div>
<script src="js/lib.js"></script>
<script>
    addChildNodes("phone_list", "", "");
    addChildNodes("birthday_list", "", "");
</script>
</body>
</html>
