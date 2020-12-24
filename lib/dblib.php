<?php

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
