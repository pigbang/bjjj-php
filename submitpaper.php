<?php
require_once 'http.php';
require_once 'curl.php';
require_once 'config.php';

function submitPaper($userid,$licenseno,$needPhoto) {
    global $host;
    global $headers;
    global $page_loadotherdrivers;
    global $page_submitpaper;
    global $appsource;
    global $platform;

    // car.json中读取车辆信息
    $car_info = loadConfig($userid.'/'.$licenseno.'/'.'car.json');
    //$licenseno = $car_info['licenseno'];
    $engineno = $car_info['engineno'];
    $cartypecode = $car_info['cartypecode'];
    $vehicletype = $car_info['vehicletype'];

    $carid = $car_info['carid'];
    $carmodel = $car_info['carmodel'];
    $carregtime = $car_info['carregtime'];

    $envGrade = $car_info['envGrade'];

    // 其余无用的信息
    $imei = '';
    $imsi = '';
    $gpslon = '';
    $gpslat = '';
    $phoneno = '';
    $code = '';

    // 从person.json中读取个人信息
    $person_info = loadConfig($userid.'/'.'person.json');
    $drivingphoto = $person_info['drivingphoto'];
    $carphoto = $person_info['carphoto'];
    $drivername = $person_info['drivername'];
    $driverlicenseno = $person_info['driverlicenseno'];
    $driverphoto = $person_info['driverphoto'];
    $personphoto = $person_info['personphoto'];
    if (!$needPhoto) {
        // 暂时不上传照片 -- 2017-9-8日启用
        $drivingphoto = '';
        $carphoto = '';
        $driverphoto = '';
        $personphoto = '';
    }
    // 进京时间选择
    $inbjentrancecode1 = '16';
    $inbjentrancecode = '13';
    $inbjduration = '7';
    // 进京时间，如果存在进京证，则从明天开始，否则是今日开始
    $inbjtime = date("Y-m-d");
    // 默认申请明天的
    {
        $tomorrow = date("Y-m-d", strtotime("$inbjtime +1 day"));
        $y = intval(date('Y', strtotime($tomorrow)));
        $m = intval(date('m', strtotime($tomorrow)));
        $d = intval(date('d', strtotime($tomorrow)));
        $inbjtime = "$y-$m-$d";
    }
    // 对时间戳取整
    $hiddentime = makeTimestampPoint();
    $date = date("Y-m-d", strtotime($hiddentime));

    // var imageId = $("#inbjentrancecode").val()+$("#inbjduration").val()+$("#inbjtime").val()+$("#userid").val()+$("#engineno").val()+$("#cartypecode").val()+$("#driverlicensenow").val()+$("#carid").val()+timestamp;
    $imageId = $inbjentrancecode.$inbjduration.$inbjtime.$userid.$engineno.$cartypecode.$driverlicenseno.$carid.$hiddentime;

    // imageId取sign
    $json_timestamp = loadConfig($userid.'/'.$date.'/'.$platform.'/'.'timestamp.json');
    // sign从json中获取
    $sign = $json_timestamp[$hiddentime];

    $form = array(
        'appsource'=>$appsource,
        'hiddentime'=>$hiddentime,
        'inbjentrancecode1'=>$inbjentrancecode1,
        'inbjentrancecode'=>$inbjentrancecode,
        'inbjduration'=>$inbjduration,
        'inbjtime'=>$inbjtime,
        'appkey'=>'',
        'deviceid'=>'',
        'token'=>'',
        'timestamp'=>'',
        'userid'=>$userid,
        'licenseno'=>$licenseno,
        'engineno'=>$engineno,
        'cartypecode'=>$cartypecode,
        'vehicletype'=>$vehicletype,
        'drivingphoto'=>$drivingphoto,
        'carphoto'=>$carphoto,
        'drivername'=>$drivername,
        'driverlicenseno'=>$driverlicenseno,
        'driverphoto'=>$driverphoto,
        'personphoto'=>$personphoto,
        'gpslon'=>$gpslon,
        'gpslat'=>$gpslat,
        'phoneno'=>$phoneno,
        'imei'=>$imei,
        'imsi'=>$imsi,
        'carid'=>$carid,
        'carmodel'=>$carmodel,
        'carregtime'=>$carregtime,
        'envGrade'=>$envGrade,
        'imageId'=>$imageId,
        'code'=>$code,
        'sign'=>$sign,
        'platform'=>$platform,
    );
    return curl_post($headers, http_build_query($form), $host.$page_submitpaper, $host.$page_loadotherdrivers);
}
?>