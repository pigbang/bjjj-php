<?php
// 设置时区
date_default_timezone_set("Asia/Shanghai");
function loadConfig($filename)
{
    $json_data = file_get_contents($filename);
    // 把JSON字符串转成PHP数组
    $config = json_decode($json_data, true);
    return $config;
}
function saveConfig($filename, $array) {
    $json_data = json_encode($array);
    file_put_contents($filename, $json_data);
}
function makeOutHtml($info) {
    $outHtml = "<p>";
    $outHtml.= $info;
    $outHtml.= "</p>";
    echo $outHtml;
}
function get_millisecond()
{
    list($usec, $sec) = explode(" ", microtime());
    $msec=round($usec*1000);
    return $msec;
}
function makeOutLog($info) {
    $file = 'log.txt';
    $now = date("Y-m-d H:i:s");
    $now.= ' ';
    $now.= sprintf('%03d', get_millisecond());
    $pid = getmypid();
    $content = "$now($pid): $info\n";
    file_put_contents($file, $content, FILE_APPEND);
}
function makeTimestampPoint() {
    // 时间相关，每6分钟采点一个
    $now = date("Y-m-d H:i:s");
    $zero = date("Y-m-d 00:00:00");
    $seconds = strtotime($now) - strtotime($zero);
    $point = ($seconds - $seconds % 360) + strtotime($zero); // 取整
    // 转成timetamp字符串
    $timestamp = date("Y-m-d H:i:s", $point);
    return $timestamp;
}

global $host;
global $headers;
global $page_index;
global $page_entercarlist;
global $page_loadotherdrivers;
global $page_submitpaper;
global $appsource;
global $appkey;
global $deviceid;
global $platform;

$host = 'https://enterbj.zhongchebaolian.com';
$domain = 'enterbj.zhongchebaolian.com';
$headers = array(
    'Host: ' . $domain,
    'Accept: */*',
    'X-Requested-With: XMLHttpRequest',
    'Accept-Encoding: gzip, deflate',
    'Accept-Language: zh-cn',
    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
    'Origin: ' . $host,
    'Connection: keep-alive',
    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 10_3_3 like Mac OS X) AppleWebKit/603.3.8 (KHTML, like Gecko) Mobile/14G60'
);
// app来源
$appsource = 'bjjj';
$appkey = 'kkk';
$deviceid = 'ddd';
// platform统一用Android类型
$platform = '02';
// 主页
$page_index = '/enterbj/jsp/enterbj/index.html';
// 获取车辆进京证状态
$page_entercarlist = '/enterbj/platform/enterbj/entercarlist';
$page_addcartype = '/enterbj/platform/enterbj/addcartype';
$page_applyBjMessage = '/enterbj/platform/enterbj/applyBjMessage';
$page_loadotherdrivers = '/enterbj-img/platform/enterbj/loadotherdrivers';
$page_submitpaper = '/enterbj/platform/enterbj/submitpaper';

?>