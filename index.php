<?php
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
require_once 'config.php';
require_once 'entercarlist.php';
require_once 'addcartype.php';
require_once 'submitpaper.php';

// 超过9点就不要自动提交了
if (isHourOver9()) {
    https(404);
    makeOutHtml("超过9点不自动提交");
    makeOutLog("超过9点不自动提交");
    return;
}

function getLicensenoByUserId($userid) {
    if (!is_file($userid.'/'.'cars.json')) {
        return null;
    }
    $json_cars = loadConfig($userid.'/'.'cars.json');
    if (count($json_cars) == 0) {
        return null;
    }
    // 目前一个账号只能申请一辆车
    $licenseno = $json_cars[0];
    return $licenseno;
}
// users.json获取多个用户
$info_users = loadConfig('users.json');
for ($i = 0; $i < count($info_users); $i++) {
    // 用户唯一标识
    $userid = $info_users[$i];
    // 需要指定申请车辆车牌号
    $licenseno = getLicensenoByUserId($userid);
    if ($licenseno == null) {
        continue;
    }
    // 当前日期
    $date = date("Y-m-d");
    // 检查时间戳目录是否存在
    global $platform;
    // token、sign存放目录
    $path = $userid.'/'.$date.'/'.$platform;
    if (!is_dir($path)) {
        makeOutHtml("User $i path not exists");
        makeOutLog("User $i path not exists");
        continue;
    }
    // 优化：读取entercarlist结果，判定是否需要申请
    if (is_file($userid.'/'.'entercarlist.json')) {
        $json_entercarlist = loadConfig($userid.'/'.'entercarlist.json');
        if (checkExistApply($json_entercarlist, $licenseno, $date)) {
            makeOutHtml("User $i no need apply, already exist one");
            makeOutLog("User $i no need apply, already exist one");
            continue;
        }
    }

    // 检查车辆列表信息
    $result_array = entercarlist($userid);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        makeOutHtml("Enter car list $i code = ".$result_array[0]);
        makeOutLog("Enter car list $i code = ".$result_array[0]);
        continue;
    }

    // 解析json，判断是否需要申请
    $data_json = json_decode($result_array[1]);
    if ($data_json->{'rescode'} != "200") {
        // 输出异常信息
        makeOutHtml("Enter car list $i rescode = ".$data_json->{'rescode'}." resdes = ".$data_json->{'resdes'});
        makeOutLog("Enter car list $i rescode = ".$data_json->{'rescode'}." resdes = ".$data_json->{'resdes'});
        continue;
    }
    // 数组，一辆车对应一个
    $datalist = $data_json->{'datalist'};
    if (count($datalist) == 0) {
        makeOutHtml("Enter car list $i no car! result = ".$result_array[1]);
        makeOutLog("Enter car list $i no car! result = ".$result_array[1]);
        continue;
    }
    // 这里我默认只有一辆车
    $carobj = null;
    // 申请车辆是否存在
    for ($j = 0; $j < count($datalist); $j++) {
        if ($datalist[$j]->{'licenseno'} == $licenseno) {
            $carobj = $datalist[$j];
            break;
        }
    }
    if ($carobj == null) {
        makeOutHtml("Enter car list $i car not exists! result = ".$result_array[1]);
        makeOutLog("Enter car list $i car not exists! result = ".$result_array[1]);
        continue;
    }
    // 优化：保存entercarlist结果，用来判定是否需要查询
    saveConfig($userid.'/'.'entercarlist.json', $data_json);
    // 是否可以申请，carinfo下边用applyflag来判断
    $applyflag = $carobj->{'applyflag'};
    if ($applyflag != '1') {
        // 正常输出
        makeOutHtml("Enter car list $i applyflag = $applyflag, 无需申请");
        makeOutLog("Enter car list $i applyflag = $applyflag, 无需申请");
        continue;
    }
    // 车辆信息
    $carid = $carobj->{'carid'};
    $applyid = '';
    if (count($carobj->{'carapplyarr'}) > 0) {
        $applyobj = $carobj->{'carapplyarr'}[0];
        $applyid = $applyobj->{'applyid'};
    }

    // 检查car.json person.json放在这里
    if (!is_file($userid.'/'.$licenseno.'/'.'car.json')) {
        // 从文件构建
        $json_car = loadConfig('userid'.'/'.'car.json');
        // 填入数据，需要请求addcartype
        if (!setAddCarType($json_car, $applyid, $carid, $userid, $licenseno)) {
            // 失败
            makeOutHtml("SetAddCarType $i failed");
            makeOutLog("SetAddCarType $i failed");
            continue;
        }
        // car.json还缺engineno
        // person.json
        $json_person = loadConfig('userid'.'/'.'person.json');
        if (!setApplyBjMessage($json_car, $json_person, $applyid, $carid, $userid, $licenseno)) {
            makeOutHtml("setApplyBjMessage $i failed");
            makeOutLog("setApplyBjMessage $i failed");
            continue;
        }
        // 保存
        file_put_contents($userid.'/'.$licenseno.'/'.'car.json', json_encode($json_car));
        file_put_contents($userid.'/'.$licenseno.'/'.'person.json', json_encode($json_person));
    }
    // 检查照片是否存在
    if (is_file($userid.'/'.$licenseno.'/'.'person.json')) {
        $json_person = loadConfig($userid.'/'.$licenseno.'/'.'person.json');
        $drivingphoto = $json_person['drivingphoto'];
        if (count($drivingphoto) == 0)
            continue;
    }
    // 是否需要提交照片
    $needPhoto = getApplyBjMessageNeedPhoto($applyid, $carid, $userid, $licenseno);
    // 提交表单
    $result_array = submitPaper($userid,$licenseno,$needPhoto);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        makeOutHtml("Submit paper $i code = ".$result_array[0]);
        makeOutLog("Submit paper $i code = ".$result_array[0]);
        continue;
    }
    // 解析json
    $paper_json = json_decode($result_array[1]);
    // 输出结果
    makeOutHtml("Submit paper $i rescode = ".$paper_json->{'rescode'}." resdes = ".$paper_json->{'resdes'});
    makeOutLog("Submit paper $i rescode = ".$paper_json->{'rescode'}." resdes = ".$paper_json->{'resdes'});
}
?>