<?php
require_once 'config.php';
require_once 'lib/dblib.php';

$dbo = dbconnect($db_dsn);
if (empty($dbo)) die('Error: データベースに接続できません');?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="keywords" content="">
        <meta name="description" content="">
        <link rel="stylesheet" href="css/base.css" type="text/css">
        <link rel="stylesheet" href="css/print.css" type="text/css">
    </head>
    <body>
        <?php 
        $n = 0;     //印刷予定枚数
        $i = 0;     //印刷実行数
        $no='three';
        $id = $_GET['ID'];
        if(isset($_GET['no'])) $no = $_GET['no'];
        $sql = "SELECT 顧客.顧客ID, 顧客.姓, 顧客.名, 顧客.セイ, 顧客.メイ, 顧客.備考,来店記録.日時,来店記録.メニュー FROM 顧客 left join 来店記録 on 顧客.顧客ID=来店記録.顧客ID where 顧客.顧客ID='${id}' order by 来店記録.日時 desc";
        $res = execute($dbo, $sql); 
        $db_data = $res->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <form>
        <input type="hidden" name="ID" value="<?=$id?>">
        <div  class="noprint center">
        印刷範囲<br><select name='no'>
            <option value='one'>1回分</option>
            <option value='two'>2回分</option>
            <option value='three' selected>3回分</option>
            </select></div>
            <div  class="center"><input type="submit" value="確定" class="noprint"></div>
            <?php 
            if($no=='one'){ 
                $n=1;
            }else if($no=='two'){
                $n=2;
            }else if($no=='three'){
                $n=3;
            }else{
                $n=0;
            }
            ?>
                <div class="page">
                        <div class="prev" id="a<?=$i?>">
                        <hr>
                        <div class="name">
                        <p><?php echo $db_data[0]["セイ"]." ".$db_data[0]["メイ"] ?>　サマ</p>
                        <p><?php echo $db_data[0]["姓"]." ".$db_data[0]["名"] ?>　様</p>
                        </div>
                    <hr>
                    </div>
                    <?php 
                    $rem=str_replace(PHP_EOL,"<br>","   ".$db_data[0]["備考"]);
                    ?>
                    <div class="rem">
                    <p><?php echo $rem ?></p> <!-- // 改行処理があると良い？ -->  
                    </div>
                    <hr><?php
            while($i<$n) {
                ?>
                <?php if(!empty($db_data[$i]["日時"])) {?> 
                    <div class="details">
                    <div class="date">
                    <p><?php echo substr($db_data[$i]["日時"],0,10) ?></p>
                    <p><?php echo substr($db_data[$i]["日時"],11) ?></p>
                    </div>
                    <div class="menu">
                    <?php $menu = explode("、",$db_data[$i]["メニュー"]);
                    $j=1;
                    foreach($menu as $m){
                        echo "$j".".  "."$m<br>";
                        $j++;
                    }
                    ?></div>
                    </div>
                    <hr>
                <?php } ?>
                <?php $i++; }?>
                </div>
        </form>   
    </body>
</html>