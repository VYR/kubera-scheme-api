<?php

namespace App\Services;

use App\Exceptions\GlobalException;
use App\GlobalLogger;
use App\GlobalResponseData;
use App\RepositoryInterfaces\PaymentRepositoryInterface;
use App\ServiceInterfaces\PaymentInterface;
use Illuminate\Http\Request;

class PaymentService implements PaymentInterface
{
    use GlobalResponseData;
    use GlobalLogger;
    protected PaymentRepositoryInterface $paymentRepository;
    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }
    public function updatePaymentDetails(Request $request)
    {
        $this->logMe(message:'start updatePaymentDetails() Service',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        $data=$request->all();
        // $data['website']= $request->header('website');
        try{
            $dbStatus=$this->paymentRepository->updatePaymentDetails($data);
            if($dbStatus['status']){
                $response['statusCode']=200;
                $response['msg']= $dbStatus['msg'];
            }
            else {
                $response['statusCode']=404;
                $response['msg']= $dbStatus['msg'];
            }
            $this->logMe(message:'end updatePaymentDetails() Service',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message:json_encode($dbStatus),data:['file' => __FILE__, 'line' => __LINE__]);
            return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end updatePaymentDetails() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(),data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function addPaymentDetails(Request $request)
    {
        $this->logMe(message:'start addPaymentDetails() Service',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        $data=$request->all();
        // $data['website']= $request->header('website');
        try{
            $dbStatus=$this->paymentRepository->addPaymentDetails($data);
            if($dbStatus['status']){
                $response['statusCode']=200;
                $response['msg']= $dbStatus['msg'];
            }
            else {
                $response['statusCode']=404;
                $response['msg']= $dbStatus['msg'];
            }
            $this->logMe(message:'end addPaymentDetails() Service',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message:json_encode($dbStatus),data:['file' => __FILE__, 'line' => __LINE__]);
            return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end addPaymentDetails() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(),data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function getPaymentsByUser(Request $request)
    {
        $this->logMe(message:'start getPaymentsByUser() Service',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        $data=$request->all();
        // $data['website']= $request->header('website');
        try{
            $dbStatus=$this->paymentRepository->getPaymentsByUser($data);
            if($dbStatus['status']){
                $response['statusCode']=200;
                $response['msg']= $dbStatus['msg'];
                $response['data']= $dbStatus['data'];
            }
            else {
                $response['statusCode']=404;
                $response['msg']= $dbStatus['msg'];
            }
            $this->logMe(message:'end getPaymentsByUser() Service',data:['file' => __FILE__, 'line' => __LINE__]);
            return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end getPaymentsByUser() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(),data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function getAllPayments(Request $request)
    {
        $this->logMe(message:'start getAllPayments() Service',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        $data=$request->all();
        $data['website']= $request->header('website');
        try{
            $response['statusCode']=200;
            $response['msg']= 'Payment Details received successfully';
            $response['data']= $this->paymentRepository->getAllPayments($data);

            $this->logMe(message:'end getAllPayments() Service',data:['file' => __FILE__, 'line' => __LINE__]);
             return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end getAllPayments() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(),data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
}
