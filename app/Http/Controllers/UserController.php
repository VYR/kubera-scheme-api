<?php

namespace App\Http\Controllers;

use App\GlobalLogger;
use App\Interfaces\UserInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use GlobalLogger;
    protected UserInterface $userInterface;
    public function __construct(UserInterface $userInterface) {
        $this->logMe(isHeading:true,message:'UserController',data:['file' => __FILE__, 'line' => __LINE__]);
        $this->userInterface = $userInterface;
    }
    public function signup(Request $request){
        $this->logMe(message:'signup()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->signup($request);
    }
    public function login(Request $request){
        $this->logMe(message:'login()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->login($request);
    }
    public function updateUserDetails(Request $request){
        $this->logMe(message:'updateUserDetails()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->updateUserDetails($request);
    }
    public function completeKyc(Request $request){
        $this->logMe(message:'completeKyc()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->completeKyc($request);
    }

    public function totalUsers(Request $request){
        $this->logMe(message:'totalUsers()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->getEntireTableData($request);
    }

    public function callMicroServices(Request $request){
        $this->logMe(message:'callMicroServices()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->handleMicroServices($request);
    }
    public function sendOtpByMobile(Request $request){
        $this->logMe(message:'sendOtpByMobile()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->sendOtpByMobile($request);
    }
    public function verifyOtp(Request $request){
        $this->logMe(message:'verifyOtp()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->userInterface->verifyOtp($request);
    }

}
