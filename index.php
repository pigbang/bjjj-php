<?php
include 'http.php';
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
// 设置时区
date_default_timezone_set("Asia/Shanghai");
function curl_get($header,$url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array($status_code, $result);
}
function curl_post($header,$data,$url,$referer)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_REFERER, $referer);
    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return array($status_code, $result);
}
$host = 'https://enterbj.zhongchebaolian.com';
$headers = array(
    'Host: enterbj.zhongchebaolian.com',
    'Accept: */*',
    'Accept-Encoding: gzip, deflate, br',
    'Accept-Language:en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4,zh-TW;q=0.2',
    'Connection: keep-alive',
    'User-Agent: bjsgecl/201704061613 CFNetwork/811.4.18 Darwin/16.5.0',
    'X-Requested-With: XMLHttpRequest',
    );
$page_index = '/enterbj/jsp/enterbj/index.html';

// 表单数据基本项
$appsource = 'bjjj';
// user.json中读取账号信息
$json_user = file_get_contents('user.json');
// 把JSON字符串转成PHP数组
$user_info = json_decode($json_user, true);
$userid = $user_info['userid'];
$appkey = $user_info['appkey'];
$deviceid = $user_info['deviceid'];
$timestamp = $user_info['timestamp'];
$token = $user_info['token'];
$sign = $user_info['sign'];
$platform = $user_info['platform'];

// car.json中读取车辆信息
$json_car = file_get_contents('car.json');
// 把JSON字符串转成PHP数组
$car_info = json_decode($json_car, true);
$licenseno = $car_info['licenseno'];
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

// 获取车辆进京证状态
$page_entercarlist = '/enterbj/platform/enterbj/entercarlist';
$form = array(
    'userid'=>$userid,
    'appkey'=>$appkey,
    'deviceid'=>$deviceid,
    'timestamp'=>$timestamp,
    'token'=>$token,
    'sign'=>$sign,
    'platform'=>$platform,
    'appsource'=>$appsource
);
$head = array(
    'Host'=>'api.jinjingzheng.zhongchebaolian.com',
    'Accept'=>'*/*',
    'Accept-Encoding'=>'gzip, deflate, br',
    'Accept-Language'=>'en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4,zh-TW;q=0.2',
    'Connection'=>'keep-alive',
    'User-Agent'=>'bjsgecl/201704061613 CFNetwork/811.4.18 Darwin/16.5.0',
    'X-Requested-With'=>'XMLHttpRequest',
    'Content-Type'=>'application/x-www-form-urlencoded'
);
function makeOutHtml($info) {
    $outHtml = "<p>";
    $outHtml.= $info;
    $outHtml.= "</p>";
    echo $outHtml;
}
$huanghang = "</br>";
$result_array = array(0,null);
// 如果失败，才会每隔3秒尝试一次，尝试3次，脚本每15分钟被唤醒一次
for ($i = 0; $i < 3; $i++) {
    $result_array = curl_post($head, http_build_query($form), $host.$page_entercarlist, $host.$page_index);
    if ($result_array[0] == 200) {
        break;
    }

    echo "Enter car list ".$i." code = ".$result_array[0].$huanghang;
    sleep(3);
}
// 异常情况
if ($result_array[0] <> 200 || $result_array[1] == null) {
    // 设置应答头
    https($result_array[0]);
    // 结束运行，将日志发送到邮箱
    if ($result_array[1] != null)
        echo $result_array[1];
    return;
}
// 解析json，判断是否需要申请
$data_json = json_decode($result_array[1]);
if ($data_json->{'rescode'} != "200") {
    // 输出异常信息
    makeOutHtml($data_json->{'resdes'});
    return;
}
// 数组，一辆车对应一个
$datalist = $data_json->{'datalist'};
// 这里我默认只有一辆车
$carobj = $datalist[0];
// 是否可以申请，carinfo下边用applyflag来判断
$applyflag = $carobj->{'applyflag'};
if ($applyflag != '1') {
    makeOutHtml("applyflag != 1, 无需申请");
    return;
}
// 申请需要的参数shenqing(applyid,carid,userid,licenseno)
$applyid = $carobj->{'applyid'};
$carid = $carobj->{'carid'};
$userid = $carobj->{'userid'};
$licenseno = $carobj->{'licenseno'};
/*
$carapplyarr = $carobj->{'carapplyarr'};
$carapplyarrlength = count($carapplyarr);
// 重置applyid，每次都现取
$applyid = '';
$enterbjend = '';
if ($carapplyarr == null || $carapplyarrlength == 0) {
    // 没有进京证，需要申请
} else {
    // 当前时间
    $date = strtotime(date("Y-m-d"));
    // 当前日期是否存在进京证
    $iscurdateexist = false;
    $iscurdatelastday = false;
    // 当前日期后是否存在进京证
    $isafterdateexist = false;
    // 有进京证，需要看是否最后一日，才能申请
    for ($i = 0; $i < $carapplyarrlength; $i++) {
        $carapply = $carapplyarr[$i];
        $tmpstart = $carapply->{'enterbjstart'};
        $tmpend = $carapply->{'enterbjend'};
        $start = strtotime($tmpstart);
        $end = strtotime($tmpend);
        // 比较范围
        if ($date >= $start && $date <= $end) {
            $iscurdateexist = true;
            if ($date == $end) {
                $iscurdatelastday = true;
                $applyid = $carapply->{'applyid'};
                $enterbjend = $carapply->{'enterbjend'};
            }
        } else if ($date < $start) {
            $isafterdateexist = true;
        } else if ($date > $end) {
            // 不用管，已经失效的进京证
        } else {
            // 应该不会走到这里
        }
    }

    if ($isafterdateexist) {
        // 已有进京证，结束
        echo "存在有效的进京证，无需申请";
        return;
    }
    if ($iscurdateexist) {
        if ($iscurdatelastday) {
            // 最后一天，需要申请
        } else {
            // 无需申请
            echo "存在有效的进京证，无需申请";
            return;
        }
    } else {
        // 没有进京证，需要申请
    }
}*/

// 根据applyid来决定申请开始时间
$page_loadotherdrivers = '/enterbj-img/platform/enterbj/loadotherdrivers';
$page_submitpaper = '/enterbj-img/platform/enterbj/submitpaper';
// 从person.json中读取个人信息
$json_person = file_get_contents('person.json');
// 把JSON字符串转成PHP数组
$person_info = json_decode($json_person, true);
$drivingphoto = $person_info['drivingphoto'];
$carphoto = $person_info['carphoto'];
$drivername = $person_info['drivername'];
$driverlicenseno = $person_info['driverlicenseno'];
$driverphoto = $person_info['driverphoto'];
$personphoto = $person_info['personphoto'];
// 进京时间选择
$inbjentrancecode1 = '16';
$inbjentrancecode = '13';
$inbjduration = '7';
// 需要修改的字段：hiddentime，inbjtime，imageId，sign
$hiddentime = date("Y-m-d H:i:s");
// 由于是服务器自动提交，采用的签名从数据库获取，时间用特定的几个点
$hiddentime = date("Y-m-d 00:00:00");
// 进京时间，如果存在进京证，则从明天开始，否则是今日开始
$inbjtime = date("Y-m-d");
// 默认申请明天的
{
    $tomorrow = date_create($inbjtime);
    date_add($tomorrow, date_interval_create_from_date_string("1 days"));
    $inbjtime = date_format($tomorrow,"Y-m-d");
}
// var imageId = $("#inbjentrancecode").val()+$("#inbjduration").val()+$("#inbjtime").val()+$("#userid").val()+$("#engineno").val()+$("#cartypecode").val()+$("#driverlicensenow").val()+$("#carid").val()+timestamp;
$imageId = $inbjentrancecode.$inbjduration.$inbjtime.$userid.$engineno.$cartypecode.$driverlicenseno.$carid.$hiddentime;
// 从文件中读取数据到PHP变量
$json_string = file_get_contents('timestamp_sign.json');
// 把JSON字符串转成PHP数组
$allTS = json_decode($json_string, true);
// sign从json中获取
$sign = $allTS[$hiddentime];
if ($sign == '' || $sign == null) {
    makeOutHtml("缺少sign值，通知管理员更新json文件");
    return;
}
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
$result_array = array(0,null);
// 尝试3次，间隔3秒，脚本每15分钟执行一次
for ($i = 0; $i < 3; $i++) {
    $result_array = curl_post($head, http_build_query($form), $host.$page_submitpaper, $host.$page_loadotherdrivers);
    if ($result_array[0] == 200) {
        if ($result_array[1] != null) {
            $tmp_json = json_decode($result_array[1]);
            // 查看rescode
            $res_code = $tmp_json->{'rescode'};
            if ($res_code == "200") {
                // success
                makeOutHtml($tmp_json->{'resdes'});
                break;
            } else {
                // failed
                makeOutHtml($tmp_json->{'resdes'});
                break;
            }
        }
    } else if ($result_array[0] == 302) {
        // 页面被暂时移除了，应该是不让提交
        makeOutHtml("页面被暂时移除了，应该是不让提交");
        break;
    }

    echo "Submit paper ".$i." code = ".$result_array[0].$huanghang;
    sleep(3);
}

?>