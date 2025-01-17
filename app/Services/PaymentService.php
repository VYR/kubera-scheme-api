<?php

namespace App\Services;

use App\Exceptions\GlobalException;
use App\GlobalLogger;
use App\GlobalResponseData;
use App\RepositoryInterfaces\PaymentRepositoryInterface;
use App\ServiceInterfaces\PaymentInterface;
use DateTime;
use Illuminate\Http\Request;

class PaymentService implements PaymentInterface
{
    use GlobalLogger;
    use GlobalResponseData;

    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function updatePaymentDetails(Request $request)
    {
        $this->logMe(message: 'start updatePaymentDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->paymentRepository->updatePaymentDetails($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['data'] = [$data, $dbStatus['data']];
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updatePaymentDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updatePaymentDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function addPaymentDetails(Request $request)
    {
        $this->logMe(message: 'start addPaymentDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->paymentRepository->addPaymentDetails($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end addPaymentDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end addPaymentDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getPaymentsByUser(Request $request)
    {
        $this->logMe(message: 'start getPaymentsByUser() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->paymentRepository->getPaymentsByUser($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
                $response['data'] = $dbStatus['data'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end getPaymentsByUser() Service', data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getPaymentsByUser() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getAllPayments(Request $request)
    {
        $this->logMe(message: 'start getAllPayments() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        $data['website'] = $request->header('website');
        try {
            $response['statusCode'] = 200;
            $response['msg'] = 'Payment Details received successfully';
            $response['data'] = $this->paymentRepository->getAllPayments($data);

            $this->logMe(message: 'end getAllPayments() Service', data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getAllPayments() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getKuberaCalculation(Request $request)
    {
        $this->logMe(message: 'start getKuberaCalculation() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $response['statusCode'] = 200;
            $response['msg'] = 'calculation Details received successfully';
            $data['no_paging'] = true;
            $rec = $this->paymentRepository->getAllPayments($data);
            $calculations = [];
            if (count($rec)) {
                $dbRec = $rec[0]->payment_details;
                $dbDate = $rec[0]->created_at;
                $payment_id = $rec[0]->id;
                $paymentDate = $rec[0]->created_at;
                $startDate = date('Y-m-d H:i:s', strtotime('+30 days', strtotime($dbDate)));
                $goldAPIData = $this->handleMicroServiceGetRequest('https://vibullion.com/get-gold-live-rate');
                $goldRateINR = round(floatval(((array) $goldAPIData)['0']->Ask) / 10, 2);
                $markingPercent = config('app-constants.PAYMENT.GOLD_MAKING_CHARGES');
                $goldGST = config('app-constants.PAYMENT.GOLD_GST');
                $investmentAmount = $dbRec['amount_paid'];
                $monthlyPayout = ($investmentAmount * 2) / 40; /*double investment/40 Months*/
                /*Monthly payout is divided as half principle and half intrest */
                $principleOnMonthlyPayout = $monthlyPayout / 2;
                $intrestOnMonthlyPayout = $monthlyPayout / 2;
                $openingBalance = $investmentAmount;
                $tdsOnIntrest = config('app-constants.PAYMENT.TDS_ON_INTREST');
                $tdsAmount = $intrestOnMonthlyPayout * ($tdsOnIntrest / 100);
                $InvestmentInvoiceValue = $monthlyPayout - $tdsAmount;
                $months = 0;
                for ($i = 1; $i <= 45; $i++) {

                    $date1 = new DateTime($paymentDate);

                    $date2 = new DateTime(date('Y-m-05 h:i:s A', strtotime($dbDate)));

                    $days = $date2->diff($date1)->format('%a');

                    if ($days < 30) {
                        $dbDate = date('Y-m-05 h:i:s A', strtotime('+30 days', strtotime($dbDate)));

                        continue;
                    } else {
                        $months++;
                    }

                    /**
                     *Excel part-1
                     */
                    $mcxPrice = $goldRateINR;
                    //$mcxPrice=7576;
                    $makingCharges = $mcxPrice * ($markingPercent / 100);
                    $marketPrice = $mcxPrice + $makingCharges;
                    $gstOnMarketPrice = $marketPrice * ($goldGST / 100);
                    $goldInvoiceValue = intval($marketPrice + $gstOnMarketPrice);

                    /**
                     * investment amount
                     */
                    $noOfGrams = bcdiv($InvestmentInvoiceValue, $goldInvoiceValue, 4);
                    $closingBal = $openingBalance - $intrestOnMonthlyPayout;
                    /**
                     * final value
                     */
                    $date = date('Y-m-d', strtotime($dbDate));
                    $row = [
                        'date' => $date,
                        // 'd' => $dbRec,
                        'id' => $payment_id,
                        'paymentDate' => $paymentDate,
                        'startDate' => $startDate,
                        'month' => $months,
                        'marketPrice' => round($marketPrice, 2),
                        'mcxPrice' => $mcxPrice,
                        'makingCharges' => round($makingCharges, 2),
                        'gstOnMarketPrice' => round($gstOnMarketPrice, 2),
                        'goldInvoiceValue' => $goldInvoiceValue,
                        'openingBal' => $openingBalance,
                        'receivedAmount' => $investmentAmount,
                        'monthlyPayout' => $monthlyPayout,
                        'intrest' => $intrestOnMonthlyPayout,
                        'principle' => $principleOnMonthlyPayout,
                        'closingBal' => $closingBal,
                        'tdsAmount' => $tdsAmount,
                        'investmentInvoiceValue' => $InvestmentInvoiceValue,
                        'noOfGrams' => $noOfGrams,
                        'days' => $days,
                    ];
                    array_push($calculations, $row);
                    /*closing will be opening for next month*/
                    $openingBalance = $closingBal;
                    // Convert the date string "2012-12-21" to a Unix timestamp
                    $dt = strtotime($date);
                    // Add 1 month to the given date using strtotime() function
                    // and output the result in the format "Y-m-d"
                    $dbDate = date('Y-m-05 h:i:s A', strtotime('+30 days', $dt));
                    if ($months === 40) {
                        break;
                    }
                }
                $response['data'] = $calculations;
            }

            // $response['data1']= round($goldAPIData[0]['Ask'],2);
            $this->logMe(message: 'end getKuberaCalculation() Service', data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getKuberaCalculation() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    private function handleMicroServiceGetRequest($url)
    {
        $this->logMe(message: 'start handleMicroServiceGetRequest()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $this->logMe(message: 'serviceOutput handleMicroServiceGetRequest()', data: ['url' => $url]);
        $this->logMe(message: 'serviceOutput handleMicroServiceGetRequest()', data: ['response' => $response]);
        $this->logMe(message: 'end handleMicroServiceGetRequest()', data: ['file' => __FILE__, 'line' => __LINE__]);

        return json_decode($response);

    }
}
