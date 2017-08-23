<?php
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
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

$base64 = 'data:image/png;base64,';
$img_0 = $base64.$drivingphoto;
$img_1 = $base64.$carphoto;
$img_2 = $base64.$driverphoto;
$img_3 = $base64.$personphoto;

$html = "
 <html>
   <head>
     <title>查看个人信息图片</title>
   </head>
   <body>
     <span>测试页面</span>
     <p><img src='{$img_0}' /></p>
     <p><img src='{$img_1}' /></p>
     <p><img src='{$img_2}' /></p>
     <p><img src='{$img_3}' /></p>
   </body>
 </html>
 ";

echo $html;

?>