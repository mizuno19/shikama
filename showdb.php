<?php
require_once 'config.php';
require_once 'lib/dblib.php';

function view($dbo, $table) {
    $sql = "SHOW COLUMNS FROM ${table}";
    $res = $dbo->query($sql);
    $ts = $res->fetchAll(PDO::FETCH_ASSOC);
?>
    <table border="1">
    <caption><?= $table ?>の構造</caption>
<?php
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
?>
    </table>

<?php
    $sql = "SELECT * FROM ${table}";
    $res = $dbo->query($sql);
    $data = $res->fetchAll(PDO::FETCH_ASSOC);
?>
    <table border="1">
    <caption><?= $table ?></caption>
<?php
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
?>
    </table>
<?php
}

// データベースへ接続
$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');
?>
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
<?php

view($dbo, '連絡先区分');
echo '<hr>';
view($dbo, '顧客');
echo '<hr>';
view($dbo, '電話番号');
echo '<hr>';
view($dbo, '生年月日');
echo '<hr>';
view($dbo, '来店記録');

?>
</body>
</html>
