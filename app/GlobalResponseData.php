<?php

namespace App;

use Illuminate\Support\Str;
use App\GlobalLogger;
trait GlobalResponseData
{
    function sendResponse($errCode = 404, $errMsg='', $data='', $status='') {
        $this->logMe(message:'start GlobalResponseData::sendResponse()',data:['file' => __FILE__, 'line' => __LINE__]);
        $data=[
            'message' =>  $errMsg,
            'data' =>  $data,
            'status' => Str::length($status)?$status:( $errCode===200?config('app-constants.BASIC.SUCCESS'):config('app-constants.BASIC.FAILED'))
        ];

        try{
            return response()->json($data, $errCode);
        }catch(\Exception $e){
            //return false;
            $this->logMe(message:'end login() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            return response()->json($data, $errCode);
        }
    }
}
