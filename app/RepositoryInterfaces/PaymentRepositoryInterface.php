<?php

namespace App\RepositoryInterfaces;

interface PaymentRepositoryInterface
{
    public function updatePaymentDetails(array $data);
    public function addPaymentDetails(array $data);
    public function getPaymentsByUser(array $data);
    public function getAllPayments(array $data);

}
