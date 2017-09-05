<?php
require_once 'curl.php';
require_once 'config.php';

function addcartype($applyid,$carid,$userid,$licenseno) {
    global $host;
    global $headers;
    global $page_index;
    global $page_addcartype;

    // form提交
    $form = array(
        'applyid'=>$applyid,
        'carid'=>$carid,
        'userid'=>$userid,
        'gpslon'=>'',
        'gpslat'=>'',
        'imei'=>'',
        'imsi'=>'',
        'licenseno'=>$licenseno,
        'appsource'=>'',
        'hiddentime'=>''
    );
    return curl_post($headers, http_build_query($form), $host.$page_addcartype, $host.$page_index);
}

require_once 'simple_html_dom.php';
// 解析html并赋值
function setAddCarType($json_car, $applyid, $carid, $userid, $licenseno) {
    $json_car['carid'] = $carid;
    $json_car['licenseno'] = $licenseno;

    $result_array = addcartype($applyid, $carid, $userid, $licenseno);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        // 请求失败
        return false;
    }

    // 解析html
    $html = new simple_html_dom($result_array[1]);
    // 表单
    $form = $html->find('form[id=submitForm]', 0);
    // carmodel
    $carmodel = $form->find('input[id=carmodel]', 0)->value;
    // carregtime
    $carregtime = $form->find('input[id=carregtime]', 0)->value;
    // 结束
    $html->clear();

    $json_car['carmodel'] = $carmodel;
    $json_car['carregtime'] = $carregtime;
    return true;
}
?>