<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\GlobalLogger;
use App\ServiceInterfaces\PaymentInterface;

class PaymentController extends Controller
{
    use GlobalLogger;
    protected PaymentInterface $paymentInterface;
    public function __construct(PaymentInterface $paymentInterface) {
        $this->paymentInterface = $paymentInterface;
    }
    public function updatePaymentDetails(Request $request){
        $this->logMe(message:'updatePaymentDetails()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->paymentInterface->updatePaymentDetails($request);
    }
    public function addPaymentDetails(Request $request){
        $this->logMe(message:'addPaymentDetails()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->paymentInterface->addPaymentDetails($request);
    }
    public function getPaymentsByUser(Request $request){
        $this->logMe(message:'getPaymentsByUser()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->paymentInterface->getPaymentsByUser($request);
    }
    public function getAllPayments(Request $request){
        $this->logMe(message:'getAllPayments()',data:['file' => __FILE__, 'line' => __LINE__]);
        return $this->paymentInterface->getAllPayments($request);
    }


}
