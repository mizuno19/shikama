<?php

// 全テーブル情報の表示
function viewdb($dbo) {
echo <<<"EOH"
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>DB確認</title>
        <style type="text/css">
        p {
            font-size: 0.9em;
            margin: 0;
        }
        </style>
    </head>
    <body>
EOH;    
    view_table($dbo, '連絡先区分');
    echo '<hr>';
    view_table($dbo, '顧客');
    echo '<hr>';
    view_table($dbo, '電話番号');
    echo '<hr>';
    view_table($dbo, '生年月日');
    echo '<hr>';
    view_table($dbo, '来店記録');
    
    echo '</body></html>';
}

// テーブル内容表示
function view_table($dbo, $table) {
    $sql = "SHOW COLUMNS FROM ${table}";
    $res = $dbo->query($sql);
    $ts = $res->fetchAll(PDO::FETCH_ASSOC);

    echo '<table border="1">';
    echo "<caption>${table}の構造</caption>";
    echo '<tr>';
    for ($i = 0; $i < $res->columnCount(); $i++) {
        $meta = $res->getColumnMeta($i);
        echo '<th>' . $meta['name'] . '</th>';
    }
    echo '</tr>';

    foreach ($ts as $rows) {
        echo '<tr>';
        foreach ($rows as $v) {
            echo '<td>' . $v . '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';

    $sql = "SELECT * FROM ${table}";
    $res = $dbo->query($sql);
    $data = $res->fetchAll(PDO::FETCH_ASSOC);

    echo '<table border="1">';
    echo "<caption>${table}</caption>";
    echo '<tr>';
    for ($i = 0; $i < $res->columnCount(); $i++) {
        $meta = $res->getColumnMeta($i);
        echo '<th>' . $meta['name'] . '</th>';
    }
    echo '</tr>';

    foreach ($data as $rows) {
        echo '<tr>';
        foreach ($rows as $v) {
            $v = str_replace(PHP_EOL, '[↩]', $v);
            echo "<td>$v</td>";
        }
        echo '</tr>';
    }
    echo '</table>';
}

// データベースの初期化
// $sample_flagがtrueの場合はサンプルデータを投入する
function initdb($dbo, $sample_flag=false) {

echo <<<"EOH"
    <!DOCTYPE html>
    <html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>DB初期化</title>
        <style type="text/css">
        p {
            font-size: 0.9em;
            margin: 0;
        }
        </style>
    </head>
    <body>
EOH;

    echo "<hr><p>テーブル削除</p><hr>";
    drop_table($dbo, "電話番号");
    drop_table($dbo, "連絡先区分");
    drop_table($dbo, "生年月日");
    drop_table($dbo, "来店記録");
    drop_table($dbo, "顧客");

    echo "<hr><p>テーブル作成</p><hr>";
    $sql = "create table 顧客(
        顧客ID char(7) not null,
        姓 varchar(100),
        名 varchar(100),
        セイ varchar(100),
        メイ varchar(100),
        備考 text,
        primary key(顧客ID));";
    execute($dbo, $sql, true);

    $sql = "create table 連絡先区分(
        区分ID char(1) not null,
        区分名 varchar(30) not null,
        primary key(区分ID));";
    execute($dbo, $sql, true);

    $sql = "create table 電話番号(
        顧客ID char(7) not null,
        区分ID char(1) not null,
        電話番号 varchar(11) not null,
        primary key(顧客ID,区分ID),
        foreign key (顧客ID) references 顧客(顧客ID),
        foreign key (区分ID) references 連絡先区分(区分ID));";
    execute($dbo, $sql, true);

    $sql = "create table 生年月日(
        顧客ID char(7) not null,
        登録ID int not null,
        生年月日 date ,
        続柄 varchar(10),
        primary key (顧客ID,登録ID),
        foreign key (顧客ID) references 顧客(顧客ID));";
    execute($dbo, $sql, true);

    $sql = "create table 来店記録(
        来店ID int auto_increment,
        顧客ID char(7) not null,
        日時 timestamp not null,
        人数 int,
        続柄 varchar(10),
        メニュー text,
        primary key (来店ID),
        foreign key (顧客ID) references 顧客(顧客ID));";
    execute($dbo, $sql, true);


    echo "<hr><p>初期データ投入</p><hr>";
    $sql = "insert into 連絡先区分 values
        ('1','自宅')
        , ('2','会社')
        , ('3','本人')
        , ('4','その他')";
    execute($dbo, $sql, true);

    if ($sample_flag) {
        echo "<hr><p>サンプルデータ</p><hr>";
        $sql = "insert into 顧客 values
            ('2011121', '出江田', '入太郎', 'デエタ', 'イレタロウ', '好きなもの：肉　食べられないもの：牛');";
        execute($dbo, $sql, true);
        $sql = "insert into 電話番号 values
            ('2011121', '1', '0123456789')
            , ('2011121', '3', '08011112222');";
        execute($dbo, $sql, true);
        $sql = "insert into 生年月日 values
            ('2011121', 1, '1978-01-01', '本人')
            , ('2011121', 2, '1980-04-28', '妻');";
        execute($dbo, $sql, true);

        $sql = "insert into 顧客 values
            ('2011131', '船橋', '太郎', 'フナバシ', 'タロウ', '好きなもの：おかめ納豆".PHP_EOL."嫌いなもの：ピーマン');";
        execute($dbo, $sql, true);
        $sql = "insert into 電話番号 values
            ('2011131', '1', '08022223333');";
        execute($dbo, $sql, true);
        $sql = "insert into 生年月日 values
            ('2011131', 1, '1978-01-01', '本人');";
        execute($dbo, $sql, true);

        $sql = "insert into 顧客 values
            ('2011151', '津田', '沼夫', 'ツダ', 'ヌマオ', '好きなもの：ナス');";
        execute($dbo, $sql, true);
        $sql = "insert into 生年月日 values
                ('2011151', 1, '1978-01-01', '本人')
            , ('2011151', 2, '1980-07-01', '友人')
            , ('2011151', 3, '1986-03-05', '友人')
            , ('2011151', 4, '1986-10-05', '友人');";
        execute($dbo, $sql, true);

        $sql = "insert into 顧客 values
            ('2011171', '稲毛', '浜', 'イナゲ', 'ハマ', 'アレルギー：乳製品');";
        execute($dbo, $sql, true);
        $sql = "insert into 電話番号 values
            ('2011171', '1', '1234506789')
            , ('2011171', '2', '9876543210')
            , ('2011171', '3', '08033334444');";
        execute($dbo, $sql, true);

        $sql = "insert into 来店記録 values
            (null, '2011131', '2020-11-01', 1, '本人', '白海老目光フリット、アワビ冬瓜'),
            (null, '2011131', '2020-11-07', 2, '奥様', 'ラスプリ、メイン、カネロニフォワ'),
            (null, '2011131', '2020-11-15', 2, 'お子様', 'アワビ冬瓜、スカンピグリル'),
            (null, '2011131', '2020-11-30', 1, '本人', 'メイン、ピッツォケリ');
            ";
        execute($dbo, $sql, true);
    }

    echo "</body></html>";
    // データベースを初期化したらそこで処理を止める
    die();

}

// 来店情報インサート
// $dbo: PDOインスタンス
// $id: 顧客ID, $date: 来店日時
// $number: 来店人数, $relation: 続柄, $eats: メニュー内容
// 来店IDは自動連番による記録
function insert_visit($dbo, $id, $date, $number, $relation, $eats) {
    // プリペアードステートメントを使い、インジェクション攻撃対策
    $sql = "INSERT INTO 来店記録 VALUES(null, :id, :date, :number, :relation, :eats)";
    $stmt = $dbo->prepare($sql);    // SQLを設定

    // SQL内の名前に変数の値を割り当てる
    $stmt->bindParam(":id", $id);
    $stmt->bindParam(":date", $date);
    $stmt->bindParam(":number", $number);
    $stmt->bindParam(":relation", $relation);
    $stmt->bindParam(":eats", $eats);

    // SQLを実行して結果を戻す
    return $stmt->execute();
}

// 検索ワードの取得
// $forms: 入力された検索ワード
function get_search_where_sql($forms) {
    $search_words = [];
    // ダブルクォーテーションで括った部分は保持して、あらゆる空白で分割する
    preg_replace_callback(
        '/""(*SKIP)(*FAIL)|"([^"]++)"|([^"\p{Z}\p{Cc}]++)/u',
        function (array $match) use (&$search_words) {
            $search_words[] = $match[2] ?? $match[1];
        }, $forms, -1, $_, PREG_SET_ORDER);
    // 重複を省く
    $search_words = array_values(array_unique($search_words));

    // SQL用の条件文を生成
    $where = "WHERE ";
    foreach ($search_words as $word) {
        // HTMLインジェクション対策
        $word = htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
        if ($where !== "WHERE ") $where .= " AND ";
        $search_sei = "姓 LIKE '%" . $word . "%'";
        $search_mei = "名 LIKE '%" . $word . "%'";
        $search_ksei = "セイ LIKE '%" . $word . "%'";
        $search_kmei = "メイ LIKE '%" . $word . "%'";
        // 結合
        $where .= "(${search_sei} OR ${search_mei} OR ${search_ksei} OR ${search_kmei}) ";
    }
    return $where;
}

// 顧客IDの生成
// $dbo: PDOインスタンス
function create_id($dbo) {
    // 生成した顧客IDに現在の日付を設定
    date_default_timezone_set('Asia/Tokyo');
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

// 連絡先区分の取得
function get_phone_classes($dbo) {
    return $dbo->query("SELECT 区分ID, 区分名 FROM 連絡先区分")->fetchAll(PDO::FETCH_ASSOC);
}

// データベースへ接続しPDOインスタンスを返す
function dbconnect($dsn) {
    $pdo = null;
    try {
        $pdo = new PDO($dsn['dsn'], $dsn['user'], $dsn['pass'], $dsn['opt']);
    } catch (PDOException $e) {
        return null;
    }
    return $pdo;
}

// クエリ実行
// $flagにtrueを指定するとエラーメッセージを画面に表示する
function execute($dbo, $sql, $flag=false) {
    try {
        $res = $dbo->query($sql);
        if ($flag) {
            echo "<p>【 SQL実行成功 】</p>";
            echo "<p>[ ${sql} ]</p>";
        }
        return $res;
    } catch (Exception $e) {
        if ($flag) {
            echo "<p>${sql}の実行に失敗しました。<br>";
            echo "&nbsp;&nbsp;(" . $e->getMessage() . ")</p>";
        }
        return null;
    }
}

// テーブル削除
function drop_table($dbo, $table) {
    $sql = "DROP TABLE ${table};";
    try {
        $dbo->query($sql);
        echo "<p>${table} テーブルを削除しました。</p>";
    } catch (Exception $e) {
        echo "<p>${table} テーブルの削除に失敗しました。<br>";
        echo "&nbsp;&nbsp;(" . $e->getMessage() . ")</p>";
    }
}


1;
