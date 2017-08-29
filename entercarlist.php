<?php
require_once 'http.php';
require_once 'curl.php';
require_once 'config.php';
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');

function entercarlist($userid) {
    global $host;
    global $headers;
    global $page_index;
    global $page_entercarlist;
    global $appsource;
    // 表单数据
    $user_info = loadConfig($userid.'/'.'user.json');
    $userid = $user_info['userid'];
    $appkey = $user_info['appkey'];
    $deviceid = $user_info['deviceid'];
    $timestamp = $user_info['timestamp'];
    $token = $user_info['token'];
    $sign = $user_info['sign'];
    $platform = $user_info['platform'];

    // 对时间戳取整
    $timestamp = makeTimestampPoint();
    $date = date("Y-m-d", strtotime($timestamp));

    // token和sign是timestamp相关，需要额外提供
    $token_info = loadConfig($userid.'/'.$date.'/'.'token.json');
    $token = $token_info[$timestamp];

    $sign_info = loadConfig($userid.'/'.$date.'/'.'sign.json');
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