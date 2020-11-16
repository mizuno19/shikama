<?php
require_once 'config.php';
require_once 'lib/dblib.php';

$view_html = "regist.html";

$db_dsn = [
    'dsn' => "mysql:dbname=" . Config::DB_DBNAME
                . ";host=" . Config::DB_HOST
                . ";port=" . Config::DB_PORT
                . ";charset=" . Config::DB_CHARSET,
    'user' => Config::DB_USER,
    'pass' => Config::DB_PASSWD,
    'opt' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,    // 静的プレースホルダを使う
    ]
];

$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');

// テーブルの作成確認
$sql = "create table 顧客(
    顧客ID char(7) not null,
    姓 varchar(100) not null,
    名 varchar(100) not null,
    セイ varchar(100),
    メイ varchar(100),
    備考 text,
    primary key(顧客ID));";
$res = $dbo->query($sql);
var_dump($res);

$html = get_html($view_html);
if (empty($html)) die('Error: 指定されたファイルが見つかりません');

var_dump($_GET);
if (isset($_GET['SEND'])) {
    $err_flag = false;
    $forms = array();
    foreach ($_GET as $key => $value) {
        $forms[$key] = $value;
    }

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
    if (!empty($forms['BARTHDAY'])) {
        // 1件の登録がある時はそれ以上の生年月日登録が無いかをチェック
        // 仕様次第ではキーを続柄にして誕生日を値で持たせる
        $barthday = array();
        $i = 0;
        foreach ($forms['BARTHDAY'] as $value) {
            $brelation = $forms['RBARTHDAY'][$i];
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

    echo $html;

} else {
    // 表示用データの読込
    echo $html;
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


function get_html($file) {
    $ret = null;
    $fp = fopen($file, 'r');
    while ($line = fgets($fp)) {
        $ret .= $line;
    }
    fclose($fp);

    return $ret;
}

?>
