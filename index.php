<?php

date_default_timezone_set('Asia/Tokyo');
$time = date("H:i");

$now = 0;
$noon = 0;

switch ($time) {
    case $time < date('08:29') :
        $now = 10;
        break;
    case $time < date('08:59') :
        $now = 0;
        break;
    case $time < date('10:29') :
        $now = 1;
        break; 
    case $time < date('12:09') :
        $now = 2;
        break;
    case $time < date('12:49') :
        $now = 2;
        $noon = 1;
        break;
    case $time < date('14:29') :
        $now = 3;
        break;
    case $time < date('16:09') :
        $now = 4;
        break;
    case $time < date('17:49'):
        $now = 5;
        break;
    case $time < date('19:29'):
        $now = 6;
        break;
    case $time < date('21:09') :
        $now = 7;
        break;
    case $time < date('22:29'):
        $now = 8;
        break;
    case $time < date('23:59:59') :
        $now = 9;
        break;
}

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

//大教室
$room_big = [
    '5-112',  //0
    '5-113',  //1
    '5-117',  //2
    '5-118',  //3
];

//小教室
$room_small = [
    '5-103',  //0
    '5-104',  //1
    '5-108',  //2
    '5-109',  //3
];

//2階教室
$room_2f = [
    '5-209',  //0
];

//13号館4階教室
$room_13gou = [
    '13-401',  //0
    '13-402',  //1
];


//ここからSQL

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

    //区別するときに使う 一つ先の時限 
    $gen1 = $now + 1;

    $stmt = $dbh->prepare('CALL proc_test01(:hi,:gen)');
    $hi = $week[$date];
    $stmt->bindValue(':hi', $hi , PDO::PARAM_STR);
    $gen = $now;
    $stmt->bindValue(':gen', $gen , PDO::PARAM_INT);
    // ストアドプロシージャをコール
    $stmt->execute();

    while($result = $stmt->fetch(PDO::FETCH_ASSOC)){
        $kougi_all[] =[  $result['time'] => $result['classroom']];
    }

    if(empty($kougi_all)) {
        $kougi_all[] = ['0' => '0'];
    }

    //時限を区別する
    foreach ($kougi_all as $value ) { //繰り返し
        foreach ($value as $g => $classroom ){
            if($g === $now) {
                $kougi_class[] = $classroom;
            }elseif($g === $gen1){
                $next_class[] = $classroom;
            }
        }
    }

}

    catch(PDOException $e){
        exit('データベース接続失敗。'.$e->getMessage());
    }

// 切断
$dbh = null;


if(empty($kougi_class)) {
    $kougi_class = array(0);
}
if(empty($next_class)) {
    $next_class = array(0);
}

?>
<!--HTMLを書き出したい-->
<?php  function make_box_kougi ($val, $next_status, $gen1) {  ?>
    
    <div class="box-kougi sbtn">
        <a href="schedule.php?room=<?php echo$val; ?>"></a>
        <div class="main-box">
            <div class="box-room"><?php echo $val; ?></div>
            <div class="status">講義中</div>
            <div class="time"><?php echo $next_status ?></div>
        </div>
        <div class="right">
            <div class="triangle"></div>
        </div>
    </div>
<?php } ?>

<?php  function make_box_aki ($val, $next_status, $gen1) {  ?>
    
    <div class="box-empty">
        <div class="main-box sbtn">
        <a href="schedule.php?room=<?php echo$val; ?>"></a>
            <div class="box-room-white"><?php echo $val; ?></div>
            <div class="status-white">利用可</div>
            <div class="time-white"><?php echo $next_status ?></div>
        </div>
        <div class="right">
            <div class="triangle-white"></div>
        </div>
    </div>
<?php } ?>

<?php  function make_box_fuka ($val) {  ?>
    
    <div class="box-fuka sbtn">
        <div class="main-box">
            <a href="schedule.php?room=<?php echo$val; ?>"></a>
            <div class="box-room-white"><?php echo $val; ?></div>
            <div class="status-white">利用不可</div>
            <div class="time-white">　</div>
        </div>
        <div class="right">
            <div class="triangle-white"></div>
        </div>
    </div>
<?php } ?>



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/style-560.css" media="screen and (min-width: 560px)">
    <link rel="stylesheet" href="css/style-960.css" media="screen and (min-width: 960px)">
    <link href="https://fonts.googleapis.com/css?family=M+PLUS+Rounded+1c" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

    <title>あきち</title>
</head>

<body>

    <div class="header">
        <div class="header-title-pc">
            <p>あきち｜現在の空き状況</p>
        </div>
        <div class="header-title">
            <p>現在の空き状況</p>
        </div>
    </div>
    <div class="main">
        <!-- モーダルウィンドウ 初めて来た人に表示-->
        <div class="overlay">
            <div class="btn_area">
                <div class="btn_area_title">
                    <h2>初めて利用する方へ</h2>
                </div>
                <ul>
                    <li>大学公式のサービスではありません</li>
                    <li>正確な情報ではありません</li>
                    <li>製作者は責任を取りません</li>
                    <li>友達にシェアしよう！</li>
                </ul>
                <p>あくまでも参考としてご利用ください</p>
                <div class="btn_area_footer">
                    <button>確認しました</button>
                </div>
            </div>
        </div>


        <!-- メインコンテンツ -->
        <div id="tab-area">
            <!-- 5号館13号館切り替え -->
            <div class="btns">
                <div class="b5 btn active">5号館</div>
                <div class="b13 btn">13号館</div>
            </div>
            <!-- tab-area -->
        </div>



        <div class="building-5">
            <!-- 5号館 -->
            <div class="floor-img">
                <!--フロアマップ-->
                <img src="img/1f.jpg" alt="1f">
            </div>

            <div class="classroom-big">
                <div class="kyositsu">大教室</div>
                <div class="building-5-pc">5号館</div>

                <?php 
                    if($now == 9 or $now == 10){
                        foreach($room_big as $val) {
                            make_box_fuka($val);
                        }
                    }else{
                        foreach($room_big as $val) {
                            if($noon == 1){
                                if(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }elseif(in_array ($val , $kougi_class)){
                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                            
                                make_box_kougi($val, $next_status, $gen1);
                            }else{

                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }
                        }

                    }
                ?>

            <!-- classroom-big -->
            </div>
            <div class="classroom-small">
                <div class="kyositsu">小教室</div>

                <?php 
                    if($now == 9 or $now == 10){
                        foreach($room_small as $val) {
                            make_box_fuka($val);
                        }
                    }else{
                        foreach($room_small as $val) {
                            if($noon == 1){
                                if(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }elseif(in_array ($val , $kougi_class)){
                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                            
                                make_box_kougi($val, $next_status, $gen1);
                            }else{

                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }
                        }

                    }
                ?>

            <!-- classroom-small -->
            </div>
            <div class="floor-img">
                <!--フロアマップ-->
                <img src="img/2f.png" alt="2f">
            </div>
            <div class="classroom-2floor">
                <div class="kyositsu">2階教室</div>
                
                <?php 
                    if($now == 9 or $now == 10){
                        foreach($room_2f as $val) {
                            make_box_fuka($val);
                        }
                    }else{
                        foreach($room_2f as $val) {
                            if($noon == 1){
                                if(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }elseif(in_array ($val , $kougi_class)){
                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                            
                                make_box_kougi($val, $next_status, $gen1);
                            }else{

                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }
                        }

                    }
                ?>

                <!-- classroom-2floor -->
            </div>
            <!-- building-5 -->
        </div>
        <div class="building-13">
            <!-- 13号館 -->

            <div class="classroom-4floor">
                <div class="building-13-pc">13号館</div>
                <div class="kyositsu">4階教室</div>

                <?php 
                    if($now == 9 or $now == 10){
                        foreach($room_13gou as $val) {
                            make_box_fuka($val);
                        }
                    }else{
                        foreach($room_13gou as $val) {
                            if($noon == 1){
                                if(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }elseif(in_array ($val , $kougi_class)){
                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                            
                                make_box_kougi($val, $next_status, $gen1);
                            }else{

                                if($gen1 >= 7){
                                    $next_status = '22:30まで';
                                }elseif(in_array ($val , $next_class)){
                                    $next_status = $gen1 .'限: 講義';
                                }else{
                                    $next_status = $gen1 .'限: 利用可';
                                }
                                make_box_aki($val, $next_status, $gen1);
                            }
                        }

                    }
                ?>

            </div>
            <!-- building-13 -->
        </div>
        <!-- main -->
    </div>

    

    <div class="footer">
        <div class="footer-mb">
            <p>当サイトは個人によるボランティアで構築・運用されています。大学の公式なサービスではありません。<br>
                年間を通しての授業についてのみ掲載しており、休講や急に入った講義などについては掲載していません。<br>
                掲載されている情報の正確性については保証するものではありません。あくまでも参考としてご利用ください。
            </p>
            <p>(c) Kei Yoneda</p>
            <p>Supported by Nagi Ishikura</P>
        </div>
        <div class="footer-pc">
            <p>急な講義の変更は反映されていません。あくまでも参考としてご利用ください。</p>
        </div>
        <!--footer-->
    </div>

    <!-- body -->
</body>

<script type="text/javascript">
    $(function(){
        $(".overlay").show();
        $.cookie('btnFlg') == 'on'?$(".overlay").hide():$(".overlay").show();
        $(".btn_area_footer button").click(function(){
            $(".overlay").fadeOut();
            $.cookie('btnFlg', 'on', { expires: 365,path: '/' }); //cookieの保存
        });
    });
</script>

<script>
    $('.btn').on('click', function () {
        $('.btn').removeClass('active');
        $(this).addClass('active');
    });
</script>

<script>
    $(function () {
        $('.b5').click(function () {
            $('.building-5').show();
            $('.building-13').hide();
        });
        $('.b13').click(function () {
            $('.building-13').show();
            $('.building-5').hide();
        });
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