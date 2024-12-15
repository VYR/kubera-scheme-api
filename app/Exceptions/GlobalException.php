<?php

namespace App\Exceptions;

use App\GlobalLogger;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Throwable;

class GlobalException extends Exception
{
    use GlobalLogger;
    public $errCode;
    public $errMsg;
    public $data;
    public $status;
    public function __construct($errCode = 404, $errMsg='', $data='', $status='',$request=[]) {
        $this->logMe(isHeading:true,message:'GlobalException',data:['file' => __FILE__, 'line' => __LINE__]);
        $this->errCode = $errCode;
        $this->errMsg = $errMsg;
        $this->data = $data;
        $this->status = $status;
        $this->render($request);
    }

    function render($request)
    {
        $data=[
            'message' =>  $this->errMsg,
            'data' =>  $this->data,
            'errCode' =>  $this->errCode,
            'status' => Str::length($this->status)?$this->status:( $this->errCode===200?config('app-constants.BASIC.SUCCESS'):config('app-constants.BASIC.FAILED'))
        ];
        $this->logMe(isHeading:true,message:'render()',data:['file' => __FILE__, 'line' => __LINE__]);
        $this->logMe(message:'ERROR',data:[
            'errCode'=> $this->errCode,
            'errMsg'=> $this->errMsg.'-----at-----',
            'file' => __FILE__,
            'line' => __LINE__
        ]);
       // return new GlobalExceptionHandler(errCode: $this->errCode,errMsg:$this->errMsg,data:$this->data,status:$this->status);
       return response()->json($data, $this->errCode);
    }

}

