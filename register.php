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
$err_flag = false;

// POSTでデータが送信されていれば登録処理
if (isset($_POST['SEND'])) {
    // 登録確認処理
?>
    <h2>●登録内容確認</h2>
    <form action="" method="POST">
        <fieldset>
        <legend>顧客情報</legend>
        <?php
        if (empty($_POST['SEI'])) { 
            $err_flag = true; ?>
            <label>姓：<input value="<?= $_POST['SEI'] ?>" type="text" name="SEI" size="10" maxlength="20"></label>
        <?php } else { ?>
            <label>姓：<input value="<?= $_POST['SEI'] ?>" type="hidden" name="SEI"><?= $_POST['SEI'] ?></label><br>
        <?php } ?>
        <?php
        if (empty($_POST['MEI'])) {
            $err_flag = true; ?>
            <label>名：<input value="<?= $_POST['MEI'] ?>" type="text" name="MEI" size="10" maxlength="20"></label>
        <?php } else { ?>
            <label>名：<input value="<?= $_POST['MEI'] ?>" type="hidden" name="MEI"><?= $_POST['MEI'] ?></label><br>
        <?php } ?>
        <?php
        if (empty($_POST['KANASEI'])) {
            $err_flag = true; ?>
            <label>セイ：<input value="<?= $_POST['KANASEI'] ?>" type="text" name="KANASEI" size="10" maxlength="20"></label><br>
        <?php } else { ?>
            <label>セイ：<input value="<?= $_POST['KANASEI'] ?>" type="hidden" name="KANASEI"><?= $_POST['KANASEI'] ?></label><br>
        <?php } ?>
        <?php
        if (empty($_POST['KANAMEI'])) {
            $err_flag = true; ?>
            <label>メイ：<input value="<?= $_POST['KANAMEI'] ?>" type="text" name="KANAMEI" size="10" maxlength="20"></label><br>
        <?php } else { ?>
            <label>メイ：<input value="<?= $_POST['KANAMEI'] ?>" type="hidden" name="KANAMEI"><?= $_POST['KANAMEI'] ?></label><br>
        <?php } ?>

        <label>好み：<input value="<?= $_POST['LIKE'] ?>" type="hidden" name="LIKE"><?= $_POST['LIKE'] ?></label><br>

        <label>連絡先</label>
<?php

    // 電話番号と区分を一つの配列にする
    if (!empty($_POST['PHONE'])) {
        $phones = array();
        $i = 0;
        foreach ($_POST['PHONE'] as $value) {
            $pclass = $_POST['PHONECLASS'][$i];
            $phones += array($i => array($value, $pclass));
            $i++;
        }

        foreach ($phones as $phone) {
?>
            <input value="<?= $phone[0] ?>" type="hidden" name="PHONE[]">
            <input value="<?= $phone[1] ?>" type="hidden" name="PHONECLASS[]">
            <p><?= $phone[0] ?>(<script>document.write(phoneClasses[<?= ($phone[1] - 1) ?>]);</script>)</p>
<?php
        }
    } else {
        $err_flag = true;
    }
    
    // 生年月日と続柄を一つの配列にする
    if (!empty($_POST['BIRTHDAY'])) {
        $birthday = array();
        $i = 0;
        foreach ($_POST['BIRTHDAY'] as $value) {
            $brelation = $_POST['RBIRTHDAY'][$i];
            $birthday += array($i => array($value, $brelation));
            $i++;
        }

        foreach ($birthday as $birth) {
?>
            <input value="<?= $birth[0] ?>" type="hidden" name="BIRTHDAY[]">
            <input value="<?= $birth[1] ?>" type="hidden" name="RBIRTHDAY[]">
            <p><?= $birth[0] ?>(<?= $birth[1] ?>])</p>
<?php
        }
    }

    if ($err_flag) echo "入力項目にエラーがあります";
?>
    </fieldset>
    <?php if ($err_flag) { ?>
        <input type="submit" name="SEND" value="確　認">
    <?php } else { ?>
        <input type="submit" name="REGIST" value="登　録">
    <?php } ?>
</form>

<?php
} else if(isset($_POST['REGISTER'])) {
    // 登録処理
    // 登録確認処理
    $err_flag = false;      // エラーフラグ
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

    if (empty($forms['SEI']) || empty($forms['MEI'])) {
        $err_flag = true;
    }

    if (empty($forms['KANASEI']) || empty($forms['KANAMEI'])) {
        $err_flag = true;
    }

    // 電話番号と区分を一つの配列にする
    if (!empty($forms['PHONE'])) {
        $phone = array();
        $i = 0;
        foreach ($forms['PHONE'] as $value) {
            $pclass = $forms['PHONECLASS'][$i];
            $phone += array($i => array($value, $pclass));
            $i++;
        }
    } else {
        $err_flag = true;
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

    if ($err_flag) echo "入力項目にエラーがあります";

    // データベースへ登録
    insert_clients($dbo, $forms);
} else {
    // 登録フォームの表示
?>
<form action="" method="POST">
<fieldset>
    <legend>顧客情報</legend>
    <div id="name">
        <label><span>姓：</span><input value="船橋" type="text" name="SEI" size="8" maxlength="20"></label>
        <label><span>名：</span><input value="太郎" type="text" name="MEI" size="8" maxlength="20"></label>
    </div>
    <div id="kana">
        <label><span>セイ：</span><input value="フナバシ" type="text" name="KANASEI" size="8" maxlength="40" required="required"></label>
        <label><span>メイ：</span><input value="タロウ" type="text" name="KANAMEI" size="8" maxlength="40" required="required"></label>
    </div>
    <div id="like">
        <label><span>備考(好みなど)：</span><textarea name="LIKE">好みのデータ</textarea></label>
    </div>
    <div id="phone">
        <button class="btn" type="button" onClick="addChildNodes('phone_list')">＋</button>
        <span>連絡先：</span>

        <div id="phone_list">
            <div id="phone1">
                <button class="btn" type="button" onClick="removeChildNodes('phone_list', 'phone1')">－</button><!--
                --><input value="08011112222" type="text" name="PHONE[]" size="10" maxlength="11">
                <span>　区分：</span><select name="PHONECLASS[]"><script>
                    for (i = 0; i < phoneClasses.length; i++) {
                        document.write("<option value=" + phoneClassesId[i] + ">" + phoneClasses[i] + "</option>");
                    }
                </script></select>
            </div>
        </div>
    </div>

    <div id="birthday">
        <button class="btn" type="button" onClick="addChildNodes('birthday_list')">＋</button>
        <span>生年月日：</span>
        <div id="birthday_list">
            <div id="birth1">
                <button class="btn" type="button" onClick="removeChildNodes('birthday_list', 'birth1')">－</button><!--
                --><input value="1979/01/01" type="text" name="BIRTHDAY[]" size="10">
                <span>　続柄：</span><input value="本人" type="text" name="RBIRTHDAY[]" size="5">
            </div>
        </div>
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
</body>
</html>
