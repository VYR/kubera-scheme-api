<?php

namespace App\ServiceInterfaces;

use Illuminate\Http\Request;

interface PaymentInterface
{
    public function updatePaymentDetails(Request $request);
    public function addPaymentDetails(Request $request);
    public function getPaymentsByUser(Request $request);
    public function getAllPayments(Request $request);


}
