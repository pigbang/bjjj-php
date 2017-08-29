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
function makeOutHtml($info) {
    $outHtml = "<p>";
    $outHtml.= $info;
    $outHtml.= "</p>";
    echo $outHtml;
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

$host = 'https://enterbj.zhongchebaolian.com';
$headers = array(
    'Host'=>$host,
    'Accept'=>'*/*',
    'Accept-Encoding'=>'gzip, deflate, br',
    'Accept-Language'=>'en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4,zh-TW;q=0.2',
    'Connection'=>'keep-alive',
    'User-Agent'=>'bjsgecl/201704061613 CFNetwork/811.4.18 Darwin/16.5.0',
    'X-Requested-With'=>'XMLHttpRequest',
    'Content-Type'=>'application/x-www-form-urlencoded'
);
// app来源
$appsource = 'bjjj';
// 主页
$page_index = '/enterbj/jsp/enterbj/index.html';
// 获取车辆进京证状态
$page_entercarlist = '/enterbj/platform/enterbj/entercarlist';
$page_loadotherdrivers = '/enterbj-img/platform/enterbj/loadotherdrivers';
$page_submitpaper = '/enterbj-img/platform/enterbj/submitpaper';

?>