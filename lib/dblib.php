<?php

function dbconnect($dsn) {
    $pdo = null;
    try {
        $pdo = new PDO($dsn['dsn'], $dsn['user'], $dsn['pass'], $dsn['opt']);
    } catch (PDOException $e) {
        return null;
    }
    return $pdo;
}


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
