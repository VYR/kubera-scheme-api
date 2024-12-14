<?php

namespace App;
use Illuminate\Support\Facades\Log;
trait GlobalLogger
{
    function logMe($type='info',$message='',$data=[],$isHeading=false){

        if($type==='debug'){
            if(count($data))
                Log::debug($this->formatLog($isHeading,$message),$data);
            else
            Log::debug($this->formatLog($isHeading,$message));
        }
        else if($type==='error'){
            if(count($data))
                Log::debug($this->formatLog($isHeading,$message),$data);
            else
            Log::debug($this->formatLog($isHeading,$message));
        }
        else{
            if(count($data))
                Log::info($this->formatLog($isHeading,$message),$data);
            else
            Log::info($this->formatLog($isHeading,$message));
        }

    }
    function formatLog($heading=false,$msg=''){
        return $heading?('***** '.$msg.' *****'):$msg;
    }
}
