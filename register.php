<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

$res = $dbo->query("SELECT 区分ID, 区分名 FROM 連絡先区分");
$classes = $res->fetchAll(PDO::FETCH_ASSOC);
$phone_classes_id = '';
$phone_classes = '';
foreach ($classes as $class) {
    $phone_classes_id .= "'" . $class['区分ID'] . "',";
    $phone_classes .= "'" . $class['区分名'] . "',";
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
    <nav>
    <ul>
        <li><a href="./"><button>顧客一覧</button></a></li>
    </ul>
    </nav>
</header>
<section id="main">

<?php
# POSTでデータが送信されていれば登録処理
$err_flag = false;
if (isset($_POST['SEND'])) {
    // 登録確認処理
?>
    <h2>登録内容確認</h2>
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
}


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
?>


<?php
if (!isset($_POST['SEND'])) {
?>
<form action="" method="POST">
    <fieldset>
    <legend>顧客情報</legend>
    <label>姓：<input value="船橋" type="text" name="SEI" size="10" maxlength="20"></label><br>
    <label>名：<input value="太郎" type="text" name="MEI" size="10" maxlength="20"></label><br>
    <label>セイ：<input value="フナバシ" type="text" name="KANASEI" size="10" maxlength="40"></label><br>
    <label>メイ：<input value="タロウ" type="text" name="KANAMEI" size="10" maxlength="40"></label><br>

    <?php
        $res = $dbo->query("SELECT 区分ID, 区分名 FROM 連絡先区分");
        $classes = $res->fetchAll(PDO::FETCH_ASSOC);
        $phone_classes_id = '';
        $phone_classes = '';
        foreach ($classes as $class) {
            $phone_classes_id .= "'" . $class['区分ID'] . "',";
            $phone_classes .= "'" . $class['区分名'] . "',";
        }
    ?>
    <script>
        const phoneClassesId = [ <?= $phone_classes_id ?> ];
        const phoneClasses = [ <?= $phone_classes ?> ];
    </script>
    <a onClick="addChildNodes('phone');">＋連絡先欄を追加</a>　
    <div id="phone">
        <label id="phone1"><a onClick="removeChildNodes('phone', 'phone1');">－</a>
            <span>連絡先：</span><input value="08011112222" type="text" name="PHONE[]" size="10" maxlength="11">
            <span>　区分：</span><select name="PHONECLASS[]"><script>
                for (i = 0; i < phoneClasses.length; i++) {
                    document.write("<option value=" + phoneClassesId[i] + ">" + phoneClasses[i] + "</option>");
                }
            </script></select>
        </label>
    </div>
    <br>

    <label>好み：<textarea rows="3" cols="35" name="LIKE">好みのデータ</textarea></label><br>

    <a onClick="addChildNodes('birthday');">＋生年月日欄を追加</a>　
    <div id="birthday">
        <label id="birth1"><a onClick="removeChildNodes('birthday', 'birth1');">－</a>
        <span>生年月日：</span><input value="1979/01/01" type="text" name="BIRTHDAY[]" size="10">
        <span>　続柄：</span><input value="本人" type="text" name="RBIRTHDAY[]" size="5"></label>
    </div>

    </fieldset>
    <input type="submit" name="SEND" value="確　認">
</form>
<?php
} else {
?>


<?php
}
?>

</section>

<footer>
    <address>

    </address>
</footer>
</div>
<script>
    // -が押されたら入力ボックスを減らす関数
    function removeChildNodes(obj, rmObj) {
        // 親要素の取得
        const parent = document.getElementById(obj);
        // 親要素内の子要素が 2 以上なら指定された子要素を削除できる
        if (parent.childElementCount > 1) {
            // 指定された子要素の削除
            document.getElementById(rmObj).remove();
        }
    }

    // +が押されたら入力ボックスを増やす関数
    function addChildNodes(obj) {
        // 親要素の取得
        const parent = document.getElementById(obj);
        //console.log(id, parent);  // 確認用

        // 追加する要素の生成
        // ラベル
        const label = document.createElement("label");
        // spanタグ
        const elm1Label = document.createElement("span");
        const elm2Label = document.createElement("span");


        if (obj === "phone") {
            // ダミーデータの生成
            var tel = "08011112222";


            // 削除用に使うIDの生成
            const rmId = "birth" + (parseInt(parent.childElementCount) + 1);

            // 追加する要素の生成
            // ラベルのIDをセット
            label.setAttribute("id", rmId)
            elm1Label.innerHTML = " 連絡先：";  // 「連絡先：」を表示するspan要素
            elm2Label.innerHTML = " 　区分：";  // 「　区分：」を表示するspan要素

            // 連絡先の入力テキストボックス
            const child = document.createElement("input");
            child.setAttribute("value", tel);  // ダミーデータ
            child.setAttribute("type", "text");
            child.setAttribute("name", "PHONE[]");
            child.setAttribute("size", "10");

            // 区分のセレクトボックス
            const classId = document.createElement("select");
            classId.setAttribute("name", "PHONECLASS[]");
            for (i = 0; i < phoneClasses.length; i++) {
                const classIdChild = document.createElement("option");
                classIdChild.setAttribute("value", phoneClassesId[i]);
                classIdChild.innerHTML = phoneClasses[i];
                classId.append(classIdChild);
            }

            // 削除用リンク
            const rmlink = document.createElement("a");
            rmlink.setAttribute("onClick", "removeChildNodes('phone', '" + rmId + "');");
            rmlink.innerHTML = "－";

            // 親要素のラベルにspanとテキストボックスを追加
            label.append(rmlink);
            label.append(elm1Label)
            label.append(child);
            label.append(elm2Label)
            label.append(classId);

        } else if (obj === "birthday") {
            // ダミーデータの生成
            var yy = Math.floor(Math.random() * (2020 - 1980)) + 1980;
            var mm = Math.floor(Math.random() * 11) + 1;
            var dd = Math.floor(Math.random() * 30) + 1;
            if (mm < 10) mm = "0" + mm;
            if (dd < 10) dd = "0" + dd;
            var dat = yy + "/" + mm + "/" + dd;
            var rSrc = [ "妻", "子", "友人", "恋人", "同僚", "上司", "部下", ];
            var r = rSrc[Math.floor(Math.random() * rSrc.length)];


            // 削除用に使うIDの生成
            const rmId = "birth" + (parseInt(parent.childElementCount) + 1);

            // 追加する要素の生成
            // ラベルのIDをセット
            label.setAttribute("id", rmId)
            
            // 「生年月日：」を表示するspan要素
            elm1Label.innerHTML = " 生年月日：";

            // 生年月日の入力テキストボックス
            const child = document.createElement("input");
            child.setAttribute("value", dat);  // ダミーデータ
            child.setAttribute("type", "text");
            child.setAttribute("name", "BIRTHDAY[]");
            child.setAttribute("size", "10");

            // 「続柄：」を表示するspan要素
            elm2Label.innerHTML = " 　続柄：";

            // 続柄の入力テキストボックス
            const rchild = document.createElement("input");
            rchild.setAttribute("value", r);         // ダミーデータ
            rchild.setAttribute("type", "text");
            rchild.setAttribute("name", "RBIRTHDAY[]");
            rchild.setAttribute("size", "5");

            // 削除用リンク
            const rmlink = document.createElement("a");
            rmlink.setAttribute("onClick", "removeChildNodes('birthday', '" + rmId + "');");
            rmlink.innerHTML = "－";

            // 親要素のラベルにspanとテキストボックスを追加
            label.append(rmlink);
            label.append(elm1Label)
            label.append(child);
            label.append(elm2Label)
            label.append(rchild);
        }
        // 親要素のdivにlabelを追加
        parent.append(label);
    }
</script>
</body>
</html>
<?php
    $dbo = null;
?>
