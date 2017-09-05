<?php
// 设置输出编码
header('Content-Type:text/html;charset=utf-8');
require_once 'config.php';
require_once 'entercarlist.php';
require_once 'addcartype.php';
require_once 'submitpaper.php';

$isSetted = false;
// users.json获取多个用户
$info_users = loadConfig('users.json');
for ($i = 0; $i < count($info_users); $i++) {
    $userid = $info_users[$i];
    // 检查use日的目录是否存在
    if (!is_dir($userid)) {
        if (!$isSetted)
            https(404);
        makeOutHtml("User $i has no folder!!!");
        makeOutLog("User $i has no folder!!!");
        $isSetted = true;
        continue;
    }
    // 检查时间戳目录是否存在
    $date = date("Y-m-d");
    if (!is_dir($userid.'/'.$date)) {
        if (!$isSetted)
            https(501);
        makeOutHtml("User $i date = ".$date." has no folder!!!");
        makeOutLog("User $i date = ".$date." has no folder!!!");
        $isSetted = true;
        continue;
    }
    // 检查userid目录下的user.json car.json person.json
    if (!is_file($userid.'/'.'user.json')) {
        // 从文件构建json转数组
        $json_user = loadConfig('userid'.'/'.'user.json');
        // 填入数据，user比较简单，只有一个
        $json_user['userid'] = $userid;
        // 保存
        file_put_contents($userid.'/'.'user.json', json_encode($json_user));
    }

    // 检查车辆列表信息
    $result_array = entercarlist($userid);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        if (!$isSetted)
            https($result_array[0]);
        makeOutHtml("Enter car list $i code = ".$result_array[0]);
        makeOutLog("Enter car list $i code = ".$result_array[0]);
        $isSetted = true;
        continue;
    }

    // 解析json，判断是否需要申请
    $data_json = json_decode($result_array[1]);
    if ($data_json->{'rescode'} != "200") {
        // 输出异常信息
        if (!$isSetted)
            https($data_json->{'rescode'});
        makeOutHtml("Enter car list $i rescode = ".$data_json->{'rescode'}." resdes = ".$data_json->{'resdes'});
        makeOutLog("Enter car list $i rescode = ".$data_json->{'rescode'}." resdes = ".$data_json->{'resdes'});
        $isSetted = true;
        continue;
    }
    // 数组，一辆车对应一个
    $datalist = $data_json->{'datalist'};
    // 这里我默认只有一辆车
    $carobj = $datalist[0];
    // 车辆信息
    $carid = $carobj->{'carid'};
    $licenseno = $carobj->{'licenseno'};
    $applyid = $carobj->{'applyid'};
    // 是否可以申请，carinfo下边用applyflag来判断
    $applyflag = $carobj->{'applyflag'};
    if ($applyflag != '1') {
        // 正常输出
        if (!$isSetted)
            https(200);
        makeOutHtml("Enter car list $i applyflag = $applyflag, 无需申请");
        makeOutLog("Enter car list $i applyflag = $applyflag, 无需申请");
        $isSetted = true;
        continue;
    }

    // 检查car.json person.json放在这里
    if (!is_file($userid.'/'.'car.json')) {
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
        file_put_contents($userid.'/'.'car.json', json_encode($json_car));
        file_put_contents($userid.'/'.'person.json', json_encode($json_person));
    }

    // 提交表单
    $result_array = submitPaper($userid);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        if (!$isSetted)
            https($result_array[0]);
        makeOutHtml("Submit paper $i code = ".$result_array[0]);
        makeOutLog("Submit paper $i code = ".$result_array[0]);
        $isSetted = true;
        continue;
    }
    // 解析json
    $paper_json = json_decode($result_array[1]);
    // 输出结果
    if (!$isSetted)
        https(201);
    makeOutHtml("Submit paper $i rescode = ".$paper_json->{'rescode'}." resdes = ".$paper_json->{'resdes'});
    makeOutLog("Submit paper $i rescode = ".$paper_json->{'rescode'}." resdes = ".$paper_json->{'resdes'});
    $isSetted = true;
}
?>