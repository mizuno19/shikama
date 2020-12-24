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
    return $stmt->execute();
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
