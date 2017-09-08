<?php
require_once 'curl.php';
require_once 'config.php';

function entercarlist($userid) {
    global $host;
    global $headers;
    global $page_index;
    global $page_entercarlist;
    global $appsource;
    global $appkey;
    global $deviceid;
    global $platform;

    // 对时间戳取整
    $timestamp = makeTimestampPoint();
    $date = date("Y-m-d", strtotime($timestamp));

    // token和sign是timestamp相关，需要额外提供
    $token_info = loadConfig($userid.'/'.$date.'/'.$platform.'/'.'token.json');
    $token = $token_info[$timestamp];

    $sign_info = loadConfig($userid.'/'.$date.'/'.$platform.'/'.'sign.json');
    $sign = $sign_info[$timestamp];

    // form提交
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
    return curl_post($headers, http_build_query($form), $host.$page_entercarlist, $host.$page_index);
}

function checkExistApply($json_entercarlist,$licenseno,$date) {
    // 数组，一辆车对应一个
    $datalist = $json_entercarlist['datalist'];
    if (count($datalist) == 0) {
        return false;
    }
    // 这里我默认只有一辆车
    $carobj = null;
    // 申请车辆是否存在
    for ($m = 0; $m < count($datalist); $m++) {
        if ($datalist[$m]['licenseno'] == $licenseno) {
            $carobj = $datalist[$m];
            break;
        }
    }
    if ($carobj == null) {
        return false;
    }

    // 车辆信息
    $carapplyarr = $carobj['carapplyarr'];
    $now = strtotime($date);
    for ($n = 0; $n < count($carapplyarr); $n++) {
        $applyobj = $carapplyarr[$n];
        // 起始-结束时间
        $enterbjstart = $applyobj['enterbjstart'];
        $enterbjend = $applyobj['enterbjend'];
        $start = strtotime($enterbjstart);
        $end = strtotime($enterbjend);
        // 状态码
        $status = $applyobj['status'];
        if ($status == "1" && ($now >= $start && $now <= $end)) {
            return true;
        }
    }
    return false;
}
?>