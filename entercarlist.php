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
?>