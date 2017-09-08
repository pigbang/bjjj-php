<?php
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
/**
 * Created by PhpStorm.
 * User: zeonadmin
 * Date: 2017/8/22
 * Time: 15:32
 */

// post sign:
// userid: userid
// type: token/sign/timestamp
// date: yyyy-mm-dd
// platform: 01/02 iOS/Android
// input: json string
$userid = $_GET['userid'];
$type = $_GET['type'];
$date = $_GET['date'];
$platform = $_GET['platform'];
$input = file_get_contents("php://input");

if (strlen($userid) == 0 || strlen($type) == 0 || strlen($date) == 0 || strlen($platform) == 0 || strlen($input) == 0) {
    echo $input;
    exit(-1);
}

// 创建userid目录
$path = $userid;
if (!is_dir($path)) {
    mkdir($path);
}
// date目录
$path.= '/'.$date;
if (!is_dir($path)) {
    mkdir($path);
}
// platform目录
$path.= '/'.$platform;
if (!is_dir($path)) {
    mkdir($path);
}
$path.= '/';

$filename = $path.$type.'.json';
if (is_file($filename)) {
    unlink($filename);
}
file_put_contents($filename, $input);
echo $input;

?>