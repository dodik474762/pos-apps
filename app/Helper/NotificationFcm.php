<?php

use App\models\master\UserDeviceSn;
use Illuminate\Support\Facades\DB;

function send($message = "", $to = "",$dataParam = array()){

    $content      = array(
        "en" => $message
    );
    $headings      = array(
        "en" => 'MOTASA PRIME APP'
    );
    $fields = array(
        'app_id' => "2ac2f564-0ffc-454d-a426-cd0c6f842b22",
        'include_player_ids' => array(
            $to
        ),
        'data' => empty($dataParam) ? array( "data" => "",) : $dataParam,
        'contents' => $content,
        'headings' => $headings,
    );

    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic MTAwZmY1MTgtNjE5Mi00OTM0LTg0YjQtNWVhMGFkOThiNTlj'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function sendNotification($message = "", $to = [],$dataParam = array()){

    $content      = array(
        "en" => $message
    );
    $headings      = array(
        "en" => 'MOTASA PRIME APP'
    );
    $fields = array(
        'app_id' => "2ac2f564-0ffc-454d-a426-cd0c6f842b22",
        'include_player_ids' => $to,
        'data' => empty($dataParam) ? array( "data" => "",) : $dataParam,
        'contents' => $content,
        'headings' => $headings,
    );

    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic MTAwZmY1MTgtNjE5Mi00OTM0LTg0YjQtNWVhMGFkOThiNTlj'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function sendBroadCast($message = "", $to = "",$dataParam = array()){

    $content      = array(
        "en" => $message
    );
    $headings      = array(
        "en" => 'MOTASA PRIME APP'
    );
    $fields = array(
        'app_id' => "2ac2f564-0ffc-454d-a426-cd0c6f842b22",
        'included_segments' => array(
        	'Subscribed Users'
        ),
        'data' => empty($dataParam) ? array( "data" => "",) : $dataParam,
        'contents' => $content,
        'headings' => $headings,
    );

    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Authorization: Basic MTAwZmY1MTgtNjE5Mi00OTM0LTg0YjQtNWVhMGFkOThiNTlj'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function getPlayerId($nik = ""){
    $getUserInformation = DB::table('users')->where('nik',$nik)->first();
    return $getUserInformation;
}

function getAllPlayerId($nik = ''){
    $idPlayer = [];
    $data = UserDeviceSn::where('u.nik', $nik)
    ->distinct()
    ->select([
        'users_device_sn.id_device'
    ])
    ->join('users as u', 'u.id', 'users_device_sn.id_user')
    ->get()->toArray();
    if(!empty($data)){
        foreach ($data as $key => $value) {
            $idPlayer[] = $value['id_device'];
        }
    }
    return $idPlayer;
}
