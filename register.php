<?php
require_once 'config.php';
require_once 'lib/dblib.php';

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

# POSTでデータが送信されていれば登録処理
if (isset($_POST['SEND'])) {
    $err_flag = false;      // エラーフラグ
    $forms = array();       // 空の配列を準備
    // 列名をキーにして連想配列を作成
    foreach ($_POST as $key => $value) {
        $forms[$key] = $value;
    }
    // 確認用
    echo "<hr>";
    var_dump($forms);
    echo "<hr>";

    if (!empty($forms['SEI'])) {
        $sei = $forms['SEI'];
    } else {
        $err_flag = true;
    }
    if (!empty($forms['MEI'])) {
        $name = $forms['MEI'];
    } else {
        $err_flag = true;
    }

    if (!empty($forms['KANASEI']) && !empty($forms['KANAMEI'])) {
        $kana = $forms['KANASEI'] . '　' . $forms['KANAMEI'];
    } else {
        $err_flag = true;
    }
    if (!empty($forms['PHONE'])) {
        $phone = $forms['PHONE'];
        $phone_class = "";
        if (!empty($forms['PHONECLASS'])) {
            $phone_class = $forms['PHONECLASS'];
        }
    }
    if (!empty($forms['LIKE'])) {
        $like = $forms['LIKE'];
    }
    if (!empty($forms['BIRTHDAY'])) {
        // 1件の登録がある時はそれ以上の生年月日登録が無いかをチェック
        // 仕様次第ではキーを続柄にして誕生日を値で持たせる
        $barthday = array();
        $i = 0;
        foreach ($forms['BIRTHDAY'] as $value) {
            $brelation = $forms['RBIRTHDAY'][$i];
            $barthday += array($i => array($value, $brelation));
            $i++;
        }
    }
    echo "<hr>誕生日データ(可変長なので表示して確認)<br>";
    var_dump($barthday);
    echo "<hr>";

    // 来店情報
    // 人数
    if (!empty($forms['NUMBER'])) {
        $number = $forms['NUMBER'];
    } else {
        $err_flag = true;
    }
    // 続柄
    if (!empty($forms['RELATION'])) {
        $relation = $forms['RELATION'];
    }
    // 食事内容
    if (!empty($forms['EATS'])) {
        $eats = $forms['EATS'];
    }

    if ($err_flag) echo "入力項目にエラーがあります";

    echo "<hr>変数内容確認<br>";
    echo "name = $name<br>";
    echo "kana = $kana<br>";
    echo "phone = $phone($phone_class)<br>";
    echo "like = $like<br>";
    var_dump($barthday); echo "<br>";
    echo "number = $number<br>";
    echo "relation = $relation<br>";
    echo "eats = $eats<br>";
    echo "<hr>";

    // 一旦配列にする
    $formdata = array($name, $kana, $like, $barthday, $number, $relation, $eats);

    //insert_clients($dbo, $formdata);
    
    //$sql = "SELECT * FROM shikama.clients";
    //$res = $dbo->query($sql);

} else {
    // 表示用データの読込
}
$dbo = null;


function insert_clients($db, $forms) {
    echo "<h1>インサート処理</h1>";
    var_dump($forms);
    // 先に名前で検索してIDを取得
    $sql = "SELECT count(*) FROM shikama.clients
        WHERE name LIKE :name";
    $stmt = $db->prepare($sql);
    var_dump($stmt);
    $stmt->bindValue(':name', $forms[0]);
    $stmt->execute();
    $res = $stmt->fetchAll();
    // 1件も見つからなければ新規登録
    if ($res[0][0] <= 0) {
        // 追加処理
        // clientsテーブル
        $sql = "INSERT INTO shikama.clients(name, kana, info)
            VALUES(:name, :kana, :info)";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':name', $forms[0]);
        $stmt->bindValue(':kana', $forms[1]);
        $stmt->bindValue(':info', $forms[2]);
        $res = $stmt->execute();
        var_dump($res);
        // 連絡先が入っていれば、テーブルに追加

        // 誕生日が入っていれば、すべての誕生日をテーブルに追加

        // 来店情報を追加

    }

    echo "<hr>";
    var_dump($res);
    echo "<hr>";
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link href="css/base.css" rel="stylesheet" type="text/css">
    <link href="css/register.css" rel="stylesheet" type="text/css">
    <title>顧客管理</title>
</head>
<body>
<div id="contents">
<header>
    <h1>登録</h1>
    <nav>
    <ul>
        <li><a href="/">顧客一覧</a></li>
    </ul>
    </nav>
</header>

<section id="main">

<form action="" method="POST">
    <fieldset>
    <legend>顧客情報</legend>
    <label>姓：<input value="船橋" type="text" name="SEI" size="10" maxlength="20"></label><br>
    <label>名：<input value="太郎" type="text" name="MEI" size="10" maxlength="20"></label><br>
    <label>セイ：<input value="フナバシ" type="text" name="KANASEI" size="10" maxlength="40"></label><br>
    <label>メイ：<input value="タロウ" type="text" name="KANAMEI" size="10" maxlength="40"></label><br>
    <label>連絡先：<input value="08011112222" type="text" name="PHONE" size="10" maxlength="11">
    　区分：<input value="本人携帯" type="text" name="PHONECLASS" size="5" maxlength="10"></label><br>
    <label>好み：<textarea rows="3" cols="35" name="LIKE">好みのデータ</textarea></label><br>

    <a onClick="addChildNodes('birthday');">＋生年月日欄を追加</a>　
    <div id="birthday">
        <label id="birth1"><a onClick="removeChildNodes('birthday', 'birth1');">－</a>
        <span>生年月日：</span><input value="1979/01/01" type="text" name="BIRTHDAY[]" size="10">
        <span>　続柄：</span><input value="本人" type="text" name="RBIRTHDAY[]" size="5"></label>
    </div>

    </fieldset>
    <fieldset>
    <legend>来店情報</legend>
    <label>人数：<input value="3" type="text" name="NUMBER" size="3">人</label><br>
    <label>続柄：<input value="家族" type="text" name="RELATION" size="10"></label><br>
    <label>食事内容：<textarea rows="3" cols="35" name="EATS">ふるふる</textarea></label><br>
    </fieldset>
    <input type="submit" name="SEND" value="登　録">
</form>

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
        // ダミーデータの生成
        var yy = Math.floor(Math.random() * (2020 - 1980)) + 1980;
        var mm = Math.floor(Math.random() * 11) + 1;
        var dd = Math.floor(Math.random() * 30) + 1;
        if (mm < 10) mm = "0" + mm;
        if (dd < 10) dd = "0" + dd;
        var dat = yy + "/" + mm + "/" + dd;
        var rSrc = [ "妻", "子", "友人", "恋人", "同僚", "上司", "部下", ];
        var r = rSrc[Math.floor(Math.random() * rSrc.length)];

        // 親要素の取得
        const parent = document.getElementById(obj);
        //console.log(id, parent);  // 確認用

        // 削除用に使うIDの生成
        const rmId = "birth" + (parseInt(parent.childElementCount) + 1);

        // 追加する要素の生成
        // ラベル
        const label = document.createElement("label");
        label.setAttribute("id", rmId)
        
        // 「生年月日：」を表示するspan要素
        const birthdayLabel = document.createElement("span");
        birthdayLabel.innerHTML = " 生年月日：";

        // 生年月日の入力テキストボックス
        const child = document.createElement("input");
        child.setAttribute("value", dat);  // ダミーデータ
        child.setAttribute("type", "text");
        child.setAttribute("name", "BIRTHDAY[]");
        child.setAttribute("size", "10");

        // 「続柄：」を表示するspan要素
        const rbirthdayLabel = document.createElement("span");
        rbirthdayLabel.innerHTML = " 　続柄：";

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
        label.append(birthdayLabel)
        label.append(child);
        label.append(rbirthdayLabel)
        label.append(rchild);

        // 親要素のdivにlabelとbrを追加
        parent.append(label);
    }
</script>
</body>
</html>