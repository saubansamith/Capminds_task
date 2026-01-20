<?php
function checkTemperature($data){
    $temp = $data['value'];

    if ($temp > 100){
        $data['status'] = "HIGH";
        $data['message'] = "Fever Detected";
    }
    else{
        $data['status'] = "NORMAL";
        $data['message'] = "Temperature normal";
    }
    return $data;
}
function checkPulse($data){
    $pulse = $data['value'];
    if($pulse > 100){
        $data['status'] = "HIGH";
        $data['message'] = "Pulse rate high";
    }
    else{
        $data['status'] = "NORMAL";
        $data['message'] = "Pulse normal";
    }
    return $data;
}
function checkBloodPressure($data){
    $data['status'] = "NORMAL";
    $data['message'] = "Blood pressure normal";
    return $data;
}
?>