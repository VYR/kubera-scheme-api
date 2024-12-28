<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

interface UserInterface
{
    public function signup(Request $request);

    public function login(Request $request);

    public function updateUserDetails(Request $request);

    public function updateSettings(Request $request);

    public function getSettings(Request $request);

    public function completeKyc(Request $request);

    public function sendOtpByMobile(Request $request);

    public function verifyOtp(Request $request);

    public function getEntireTableData(Request $request);

    public function handleMicroServices(Request $request);

    public function getAadharUrl(Request $request);

    public function updateBankDetails(Request $request);

    public function updateDeliveryAddress(Request $request);

    public function addContactMessages(Request $request);

    public function updateContactMessages(Request $request);

    public function deleteUser(Request $request);
}
