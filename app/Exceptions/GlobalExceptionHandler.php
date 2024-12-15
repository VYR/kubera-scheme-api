<?php

namespace App\Exceptions;

use App\GlobalLogger;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Throwable;

class GlobalExceptionHandler implements ExceptionHandler
{
    use GlobalLogger;
    public $errCode;
    public $errMsg;
    public $data;
    public $status;
    public function __construct($errCode = 404, $errMsg='', $data='', $status='') {

        $this->errCode = $errCode;
        $this->errMsg = $errMsg;
        $this->data = $data;
        $this->status = $status;
    }
    function shouldReport(Throwable $e){

    }
    function report(Throwable $e){

        //return false;
    }

    function render($request, Throwable $e)
    {
        $this->logMe(isHeading:true,message:'GlobalExceptionHandler',data:['file' => __FILE__, 'line' => __LINE__]);
        $this->logMe(message:'start render()',data:['file' => __FILE__, 'line' => __LINE__]);
        if (!$request->is('api/*')) {
                return response()->json([
                    'message' => 'Invalid Request'
                ], 404);
        }
        if(!strlen($this->errMsg)) {
            $this->errMsg= $e->getMessage();
            //$this->errMsg= $e->getTrace();
        }
        $data=[
            'message' =>  $this->errMsg,
            'data' =>  $this->data,
            'errCode' =>  $this->errCode,
            'status' => Str::length($this->status)?$this->status:( $this->errCode===200?config('app-constants.BASIC.SUCCESS'):config('app-constants.BASIC.FAILED'))
        ];
        $this->logMe(message:'end render()',data:['file' => __FILE__, 'line' => __LINE__]);
        $this->logMe(message:'ERROR',data:[
            'errCode'=> $this->errCode,
            'errMsg'=> $this->errMsg.'-----at-----',
            'file' => __FILE__,
            'line' => __LINE__
        ]);
        return response()->json($data, $this->errCode);
    }

    function renderForConsole($output, Throwable $e)
    {
        return response()->json([]);
    }

}
