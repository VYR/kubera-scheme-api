<?php

namespace App\Repositories;
use App\Models\User;
use App\Models\Payment;
use App\Exceptions\GlobalException;
use App\GlobalLogger;
use App\RepositoryInterfaces\PaymentRepositoryInterface;
use App\Mail\EmailTemplate;
use Illuminate\Support\Facades\Mail as FacadesMail;

class PaymentRepository implements PaymentRepositoryInterface
{
    use GlobalLogger;
    public function __construct()
    {
        //
    }
    public function readDataParams($params=array()){
        $parameters=config('app-constants.paginationParams', []);
        foreach ($parameters as $key => $value) {
            if(key_exists($key,$params)){
                if($key==='pageIndex'){
                    if($params['pageIndex']===0){
                        $parameters['pageIndex']=1;
                    }
                    else if($params['pageIndex']>0){
                        $parameters['pageIndex']=$params['pageIndex']+1;
                    }
                }
                else
                $parameters[$key]=$params[$key];
            }
        }
        return $parameters;
    }
    public function updatePaymentDetails(array $data){
        $this->logMe(message:'start updatePaymentDetails() Repository',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            if(!array_key_exists('userId', $data)){
                return [
                    'msg'=> " User Id key is mandatory",
                    'status' => false
                ];
            }
            if(!array_key_exists('bank_details', $data)){
                return [
                    'msg'=> " Bank details key is mandatory",
                    'status' => false
                ];
            }
            $conditions=[
                ["id",'=', $data['userId']]
            ];
            $response=User::where($conditions)->first();
            if(is_null($response)){
                return [
                    'msg'=> "Invalid User",
                    'status' => false
                ];
            }
            else{
                $response->bank_details=$data['payment_details'];
                if ($response->save()) {
                    return [
                        'msg'=> " Payment Details Updated Successfully",
                        'status' => true
                    ];
                }
                else{
                    return [
                        'msg'=> "Unable to Update Payment Details",
                        'status' => false
                    ];
                }
            }

        }catch(\Exception $e){
            $this->logMe(message:'end updatePaymentDetails() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }

    public function addPaymentDetails(array $data){
        $this->logMe(message:'start addPaymentDetails() Repository',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            if(!array_key_exists('userId', $data)){
                return [
                    'msg'=> " User Id key is mandatory",
                    'status' => false
                ];
            }
            if(!array_key_exists('payment_details', $data)){
                return [
                    'msg'=> " Payment details key is mandatory",
                    'status' => false
                ];
            }
            $data["userId"] = intval($data["userId"]);
            $conditions=[
                ["email",'=', $data['userId']]
            ];
            $response=User::where($conditions)->first();
            if(is_null($response)){
                return [
                    'msg'=> "Invalid User",
                    'status' => false
                ];
            }
            else{
                $payment=new Payment();
                $payment->userId=$data['userId'];
                $payment->payment_details=$data['payment_details'];
                $payment->payment_history=$data['payment_details'];
                if ($payment->save()) {
                    $data=[
                        'salutation' => 'Dear '.$data['payment_details']['name'],
                        'subject' => $data['payment_details']['paymentForLabel'].' Payment - Rs.'.$data['payment_details']['amount_paid'],
                        'body' => '',
                        'details' => $data['payment_details'],
                        'template' => 'payment',
                        'to' => $data['payment_details']['email']
                    ];
                    $this->sendPaymentEmail($data);
                    return [
                        'msg'=> " Payment Completed Successfully",
                        'status' => true
                    ];
                }
                else{
                    return [
                        'msg'=> "Unable to Complete Payment",
                        'status' => false
                    ];
                }
            }

        }catch(\Exception $e){
            $this->logMe(message:'end addPaymentDetails() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function getPaymentsByUser(array $data){
        $this->logMe(message:'start getPaymentsByUser() Repository',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            if(!array_key_exists('userId', $data)){
                return [
                    'msg'=> " User Id key is mandatory",
                    'status' => false
                ];
            }

            $conditions=[
                ["userId",'=', $data['userId']]
            ];
            return [
                'data' => Payment::where($conditions)->orderByDesc('created_at')->get(),
                'msg'=> "Your payment details fetched successfully",
                'status' => true
            ];

        }catch(\Exception $e){
            $this->logMe(message:'end getPaymentsByUser() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function getAllPayments(array $data){
        $this->logMe(message:'start getAllPayments() Repository',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            // return Payment::orderByDesc('created_at')->get();
             $conditions=[];
        $pagingParams=$this->readDataParams($data);
        if(array_key_exists('userId', $data)){
            array_push($conditions,["userId",'=', $data['userId']]);
        }
        if(array_key_exists('paymentFor', $data)){
            array_push($conditions,["payment_details->paymentFor",'=', strtoupper($data['paymentFor'])]);
        }
        if(array_key_exists('txnNo', $data)){
            array_push($conditions,["payment_details->txnNo",'=', strtoupper($data['txnNo'])]);
        }
        if(array_key_exists('payment_mode', $data)){
            array_push($conditions,["payment_details->payment_mode",'=', strtoupper($data['payment_mode'])]);
        }
        if(count($conditions)>0)
            return Payment::where($conditions)->orderByDesc('created_at')->paginate($pagingParams[config('app-constants.pagingKeys.pageSize')],
            ['*'],'users',$pagingParams[config('app-constants.pagingKeys.pageIndex')]);
        else
            return Payment::orderByDesc('created_at')->paginate($pagingParams[config('app-constants.pagingKeys.pageSize')],
            ['*'],'users',$pagingParams[config('app-constants.pagingKeys.pageIndex')]);
        }catch(\Exception $e){
            $this->logMe(message:'end getAllPayments() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }

    }
    function sendPaymentEmail($data){
        $this->logMe(message:'start sendEmail()',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=['status' => true,'message' => 'sendEmail Process started'];
        $mailData = [
            'logo' => config('app-constants.IMAGES.LOGO'),
            'website' => config('app-constants.EMAILS.SITE_URL'),
            'team' => config('app-constants.EMAILS.TEAM'),
            'salutation' => $data['salutation'],
            'subject' =>  $data['subject'],
            'body' => $data['body'],
            'details' => $data['details'],
            'template' => $data['template']
        ];
        $to=$data['to'];
        $resp=FacadesMail::to(config('app-constants.EMAILS.RAO'))->send(new EmailTemplate($mailData));
        $resp=FacadesMail::to($to)->send(new EmailTemplate($mailData));

        $this->logMe(message:'end sendEmail()',data:['file' => __FILE__, 'line' => __LINE__]);
       // return $response;
    }


}
