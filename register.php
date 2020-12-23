<?php
require_once 'config.php';
require_once 'lib/dblib.php';

function create_id($dbo) {
    // 生成した顧客IDに現在の日付を設定
    $id = date("ymd");

    // 現在登録されている同日の顧客IDのうち最大値を取得
    $sql = "SELECT MAX(顧客ID) AS 顧客ID FROM 顧客 WHERE 顧客ID LIKE '" . $id . "%'";
    $res = execute($dbo, $sql);

    // クエリ実行結果のチェック
    if (!empty($res)) {
        // 結果が空でなければデータを配列で取得
        $max_id = ($res->fetchAll(PDO::FETCH_ASSOC))[0]["顧客ID"];
        if (empty($max_id)) {
            // IDの最大値がNULLならば、その日最初に登録する顧客のため末尾に1を追加する
            $id .= "1";
        } else {
            // 最大値が存在する場合は、その最大値に+1をし文字列に変換する
            $id = strval(intval($max_id) + 1);
        }

        return $id;
    }

    return null;
}

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
    // echo "<hr>";
    // var_dump($forms);
    // echo "<hr>";

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

    if (empty($forms['PHONE']) || empty($forms['PHONECLASS'])) {
        $err_flag = true;
    }
    
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


    // echo "<hr>変数内容確認<br>";
    // echo "name = $name<br>";
    // echo "kana = $kana<br>";
    // echo "phone = $phone($phone_class)<br>";
    // echo "like = $like<br>";
    // foreach ($birthday as $rows) {
    //     foreach ($rows as $key => $value) {
    //         echo "birthday = $key : $value<br>";
    //     }
    // }
    // echo "number = $number<br>";
    // echo "relation = $relation<br>";
    // echo "eats = $eats<br>";
    // echo "<hr>";

    // 一旦配列にする
    //$formdata = array($id, $sei, $mei, $k_sei, $k_mei, $like, $birthday, $number, $relation, $eats);

    // データベースへ登録
    insert_clients($dbo, $forms);
    
    //$sql = "SELECT * FROM shikama.clients";
    //$res = $dbo->query($sql);

} else {
    // 表示用データの読込
}


function insert_clients($dbo, $forms) {
    // トランザクション処理開始
    // $dbo->query("BEGIN");

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
    $phone_sql = "INSERT INTO 電話番号 VALUES(:id, :cid, :phone)";
    $phone = $dbo->prepare($phone_sql);
    $phone->bindParam(":id", $forms['ID']);
    $phone->bindParam(":cid", $forms['PHONECLASS']);
    $phone->bindParam(":phone", $forms['PHONE']);
    $res = $phone->execute();

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

    // 来店記録テーブルへ情報を追加する
    $visit_sql = "INSERT INTO 来店記録 VALUES(null, :id, :date, :number, :relation, :eats)";
    $visit = $dbo->prepare($visit_sql);
    $visit->bindParam(":id", $forms['ID']);
    $date = date("Y-m-d H:i:s");
    $visit->bindParam(":date", $date);
    $visit->bindParam(":number", $forms['NUMBER']);
    $visit->bindParam(":relation", $forms['RELATION']);
    $visit->bindParam(":eats", $forms['EATS']);
    $res = $visit->execute();

    // コミット
    // $dbo->query("COMMIT");

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
        <li><a href="./">顧客一覧</a></li>
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
    　区分：
    <select name="PHONECLASS">
    <?php
        $res = $dbo->query("SELECT 区分ID, 区分名 FROM 連絡先区分");
        $classes = $res->fetchAll(PDO::FETCH_ASSOC);
        foreach ($classes as $class) {
    ?>
            <option value="<?= $class['区分ID'] ?>"><?= $class['区分名'] ?></option>
    <?php } ?>
    </select></label><br>
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
<?php
    $dbo = null;
?>
