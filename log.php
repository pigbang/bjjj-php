<?php
/**
 * Created by PhpStorm.
 * User: zeonadmin
 * Date: 2017/8/30
 * Time: 10:54
 */

// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
function getLog() {
    $file = file("log.txt");
    foreach($file as &$line) echo $line.'<br />';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>日志文件</title>
</head>
<body>
<?php getLog() ?>
</body>
</html>