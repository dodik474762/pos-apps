<?php

use App\models\master\Actor;
use App\models\master\DocumentTransaction;
use App\models\pengguna\User;
use Illuminate\Support\Facades\Cache;

function createLog($content = '', $action = ''){
    try {
        //code...
        $user = session('user_id');
        // echo $user;die;
        $actor = new Actor();
        $actor->users = $user;
        $actor->content = json_encode($content);
        $actor->action = $action;
        $actor->save();
    } catch (\Throwable $th) {
        //throw $th;
    }
}

function createDocTransaction($content, $doc_trans, $state, $remarks = ''){
    $user = session('user_id');
    $actor = new Actor();
    $actor->users = $user;
    $actor->content = json_encode($content);
    $actor->action = 1;
    $actor->save();

    $actor_id = $actor->id;

    $doc = new DocumentTransaction();
    $doc->doc_trans = $doc_trans;
    $doc->state = $state;
    $doc->actor = $actor_id;
    if($remarks != ''){
        $doc->remarks = $remarks;
    }
    $doc->save();
}

function createDocTransactionMobile($content, $nik, $doc_trans, $state, $remarks = ''){
    $user = User::where('nik', $nik)->first();
    $actor = new Actor();
    $actor->users = $user->id;
    $actor->content = json_encode($content);
    $actor->action = 1;
    $actor->save();

    $actor_id = $actor->id;

    $doc = new DocumentTransaction();
    $doc->doc_trans = $doc_trans;
    $doc->state = $state;
    $doc->actor = $actor_id;
    if($remarks != ''){
        $doc->remarks = $remarks;
    }
    $doc->save();
}
