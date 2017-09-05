<?php
require_once 'curl.php';
require_once 'config.php';
require_once 'simple_html_dom.php';

function applyBjMessage($applyid, $carid, $userid, $licenseno, $envGrade) {
    global $host;
    global $headers;
    global $page_index;
    global $page_applyBjMessage;
    global $appsource;

    // form提交
    $form = array(
        'appsource'=>$appsource,
        'hiddentime'=>'',
        'applyid'=>$applyid,
        'userid'=>$userid,
        'applystatus'=>'',
        'carid'=>$carid,
        'gpslon'=>'',
        'gpslat'=>'',
        'imei'=>'',
        'imsi'=>'',
        'licenseno'=>$licenseno,
        'envGrade'=>$envGrade
    );
    return curl_post($headers, http_build_query($form), $host.$page_applyBjMessage, $host.$page_index);
}

function setApplyBjMessage($json_car, $json_person, $applyid, $carid, $userid, $licenseno, $envGrade="3") {
    $result_array = applyBjMessage($applyid, $carid, $userid, $licenseno, $envGrade);
    if ($result_array[0] != 200 || $result_array[1] == null) {
        // 请求失败
        return false;
    }

    // 解析html
    $html = new simple_html_dom($result_array[1]);
    // 表单
    $form = $html->find('form[id=applymes]', 0);
    // id=carenginenumber name=engineno
    $engineno = $form->find('input[id=carenginenumber]', 0)->value;
    // id=carusername name=
    $carusername = $form->find('input[id=carusername]', 0)->value;
    // id=carusernum name=
    $carusernum = $form->find('input[id=carusernum]', 0)->value;
    // 结束
    $html->clear();

    $json_car['engineno'] = $engineno;
    $json_person['drivername'] = $carusername;
    $json_person['driverlicenseno'] = $carusernum;
    return true;
}
?>