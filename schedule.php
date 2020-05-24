<?php
//GETの受け取り
$room = $_GET['room'];

date_default_timezone_set('Asia/Tokyo');
$tukinichi = date('n月d日');
$time = date("H:i");


$gen = array(
    '0',
    '1'=>'(9:00~10:30)',
    '2'=>'(10:40~12:10)',
    '3'=>'(13:00~14:30)',
    '4'=>'(14:40~16:10)',
    '5'=>'(16:20~17:50)',
    '6'=>'',
    '7'=>'',
);


$now = 0;

$week = [
  '日', //0
  '月', //1
  '火', //2
  '水', //3
  '木', //4
  '金', //5
  '土', //6
];

$date = date('w');

try {
  $connect_db = "mysql:dbname=test;host=localhost";
  $connect_user = 'mac';
  $connect_passwd = 'kome2929';

  //データベース接続
  $dbh = new PDO(
        $connect_db,
        $connect_user,
        $connect_passwd,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
  );


    $stmt = $dbh->prepare('CALL proc_schedule(:room,:youbi)');
    
    $stmt->bindValue(':room', $room , PDO::PARAM_STR);
    $youbi = $week[$date];
    $stmt->bindValue(':youbi', $youbi , PDO::PARAM_STR);
    // ストアドプロシージャをコール
    $stmt->execute();

    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
        $num[] = $result['time'];
        $subject[] = $result['subject'];
        $teacher[] = $result['teacher'];
    }

}
    catch(PDOException $e){
        exit('データベース接続失敗。'.$e->getMessage());
    }

// 切断
$dbh = null;

if(empty($num)) {
    $num = array(0);
    $subject = array(0);
    $teacher = array(0);
}

?>
<!-- function -->
<?php function make_li_kogi ($value,$subject,$gen,$i,$teacher) { ?>
<li>
    <div class="sub"><?php echo $subject[$value] ?></div>
    <div class="time"><br> <?php echo $teacher[$value]?> <?php echo $gen[$i] ?></div>
</li>
<?php } ?>

<?php function make_li_aki () { ?>
<li>
    <div class="sub"></div>
    <div class="time"></div>
</li>
<?php } ?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <link rel="stylesheet" href="css/schedule-style.css">
    <link rel="stylesheet" href="css/schedule-560.css" media="screen and (min-width: 560px)">
    <link rel="stylesheet" href="css/schedule-960.css" media="screen and (min-width: 960px)">
    <link href="https://fonts.googleapis.com/css?family=M+PLUS+Rounded+1c" rel="stylesheet">

    <title>あきち | 利用予定</title>
</head>

<body>

    <div class="header">
        <div class="header-back">
            <div class="triangle-white sbtn">
                <a href="index.php"></a>
            </div>
        </div>
        <div class="header-title">
            <p><?php echo $room; ?>教室</p>
        </div>
    </div>
    <div class="main">
        <!-- メインコンテンツ -->

        <!--利用予定　日にち-->
        <div class="day"><?php echo $week[$date] ?>曜日の利用予定</div>



        <!--利用予定　日にち-->
        <div class="list-gen">
            <ol>

                <?php 
                    $value = 0;
                    for($i = 1; $i < 8; $i++) {
                        if( in_array($i , $num) ){
                            make_li_kogi($value,$subject,$gen,$i,$teacher);
                            $value++;
                        }else{
                            make_li_aki();
                        }
                    }
                    
                ?>

            </ol>
        </div>

        <!--日付切り替え　ボタン-->
        <!--<div class="option">
            <ul>　
                <li class="day-btn">前日</li>
                <li class="day-btn">今日</li>
                <li class="day-btn">翌日</li>
            </ul>
        </div> -->

        <a class="back-btn" href="index.php">もどる</a> 



        <!-- main -->
    </div>

    <div class="footer">
        <!-- footer -->
        <p>当サイトは大学ホームページに掲載している時間割を使用しています。</p>
        <p>当サイトは個人によるボランティアで構築・運用されています。大学の公式なサービスではありません。<br>
            年間を通しての授業についてのみ掲載しており、休講や急に入った講義などについては掲載していません。<br>
            掲載されている情報の正確性については保証するものではありません。あくまでも参考としてご利用ください。</p>

        <p>(c) Kei Yoneda</p>
        <p>Supported by Nagi Ishikura</P>
    </div>

    <!-- body -->
</body>

<script>
    $('.btn').on('click', function () {
        $('.btn').removeClass('active');
        $(this).addClass('active');
    });
</script>



<script>
    $(document).ready(function () {

        $(".sbtn").click(function () {
            if ($(this).find("a").attr("target") == "_blank") {
                window.open($(this).find("a").attr("href"), '_blank');
            } else {
                window.location = $(this).find("a").attr("href");
            }
            return false;
        });

    })

</script>

</html>