<?php

namespace App\Services;

use App\Exceptions\GlobalException;
use App\GlobalLogger;
use App\GlobalResponseData;
use App\Interfaces\UserInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserService implements UserInterface
{
    use GlobalLogger;
    use GlobalResponseData;

    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->logMe(isHeading: true, message: 'UserService', data: ['file' => __FILE__, 'line' => __LINE__]);
        $this->userRepository = $userRepository;
    }

    public function signup(Request $request)
    {
        $this->logMe(message: 'start signup()', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => '',
        ];
        /** Prepare model or table or DB Data */
        $data = [];
        try {
            $data['email'] = time().random_int(1000000, 9999999);
            $data['password'] = FacadesHash::make($request->password);
            $data['user_details'] = ['signup_data' => [
                ...$request->all(),
                'website' => $request->header('website'),
                'userId' => $data['email'],
                'status' => 'Active',
            ]];
            /**Call micro service */
            $services = config('app-constants.MICRO_SERVICES');
            $serviceName = $services['WEBSITES'][$request->header('website')];
            $prepareUrl = $services[$serviceName]['URL'].$services[$serviceName]['SIGNUP'];
            // $response['data']=$data;
            /** Call DB operations */
            $dbStatus = $this->userRepository->signup($data);
            // $response['data']['dbStatus']=$dbStatus;
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = config('app-constants.RESPONSE.MSG.SIGNUP.SUCCESS');
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['data'];
                // Get the currently authenticated user...
                $user = Auth::user();
                $response['data']['dbStatus'] = $user;
                // Get the currently authenticated user's ID...
                // $id = Auth::id();
            }
            $this->logMe(message: 'end signup()', data: ['file' => __FILE__, 'line' => __LINE__]);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            //return false;
            $this->logMe(message: 'end signup() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: [], errMsg: $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $this->logMe(message: 'start login()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        $data['website'] = $request->header('website');
        try {
            $dbStatus = $this->userRepository->login($data);
            if ($dbStatus) {
                $response['statusCode'] = 200;
                $response['msg'] = config('app-constants.RESPONSE.MSG.LOGIN.SUCCESS');
                $a = $dbStatus['user']->toArray();
                unset($a['user_details']['signup_data']['password']);
                $dbStatus['user'] = $a;
                $response['data'] = $dbStatus;

            } else {
                $response['statusCode'] = 404;
                $response['msg'] = config('app-constants.RESPONSE.MSG.LOGIN.FAILED');
                $response['data'] = [];
            }
            $this->logMe(message: 'end login()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end login() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateUserDetails(Request $request)
    {
        $this->logMe(message: 'start updateUserDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->userRepository->updateUserDetails($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updateUserDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updateUserDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateSettings(Request $request)
    {
        $this->logMe(message: 'start updateSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        try {
            $dbStatus = $this->userRepository->updateSettings($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updateSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updateSettings() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function completeKyc(Request $request)
    {
        $this->logMe(message: 'start completeKyc()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->userRepository->completeKyc($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end completeKyc()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end completeKyc() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getSettings(Request $request)
    {
        $this->logMe(message: 'start getSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'statusCode' => 200,
        ];
        $data = $request->all();

        try {
            $dbStatus = $this->userRepository->getSettings($data);
            $response['data'] = $dbStatus;
            $response['msg'] = config('app-constants.RESPONSE.MSG.GET_DATA_SUCCESSFUL');
            $this->logMe(message: 'end getSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getSettings() at Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(data: $response, errMsg: $e->getMessage());
        }
    }

    public function getEntireTableData(Request $request)
    {
        $this->logMe(message: 'start getEntireTableData()', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'statusCode' => 200,
        ];
        $data = $request->all();
        if ($request->hasHeader('website')) {
            $data['website'] = $request->header('website');
        }
        try {
            $dbStatus = $this->userRepository->getEntireTableData($data);
            $response['data'] = ['content' => $dbStatus, 'totalRecords' => count($dbStatus)];
            $response['msg'] = config('app-constants.RESPONSE.MSG.GET_DATA_SUCCESSFUL');
            $this->logMe(message: 'end getEntireTableData()', data: ['file' => __FILE__, 'line' => __LINE__]);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getEntireTableData() at Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(data: $response, errMsg: $e->getMessage());
        }
    }

 // Get authenticated user
    public function getUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found'],401);
            }
        } catch (JWTException $e) {
            return response()->json(['message' => 'Invalid token'],401);
        }
        $user=$user->toArray();
        unset($user['user_details']['signup_data']['password']);
        return $user['user_details']['signup_data'];
    }

    public function handleMicroServices(Request $request)
    {
        $this->logMe(message: 'start handleMicroServices()', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        try {
            $services = config('app-constants.MICRO_SERVICES');
            $requestMethod = $request->method();
            $this->logMe(message: 'Invalid Method', data: ['method' => $requestMethod, 'line' => __LINE__]);
            $serviceName = $request->route()->parameter('serviceName');
            $segment1 = $request->route()->parameter('segment1');
            $segment2 = $request->route()->parameter('segment2');
            if (! array_key_exists($serviceName, $services)) {
                $response['msg'] = 'Invalid Micro Service';
                $response['statusCode'] = 404;

                return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
            }
            $response['data']['microService'] = $serviceName;
            $response['data']['method'] = $requestMethod;
            $prepareUrl = $services[$serviceName]['URL'];
            if ($segment1) {
                $prepareUrl = $prepareUrl.$segment1;
            }
            if ($segment2) {
                $prepareUrl = $prepareUrl.'/'.$segment2;
            }
            $params = $request->all();
            if ($requestMethod === 'GET') {
                if (count($params) > 0) {
                    $prepareUrl = $prepareUrl.'?'.http_build_query($params);
                }

                return $this->handleMicroServiceGetRequest($prepareUrl);
            } elseif ($requestMethod === 'POST') {
                if (count($params) > 0) {
                    return $this->handleMicroServicePostRequest($prepareUrl, $params);
                } else {
                    $response['msg'] = 'Post Request must have Data';
                    $response['statusCode'] = 404;
                    $this->logMe(message: 'Post Request must have Data', data: ['file' => __FILE__, 'line' => __LINE__]);

                    return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
                }
            } else {
                $response['msg'] = 'Invalid Method';
                $response['statusCode'] = 404;
                $this->logMe(message: 'Invalid Method', data: ['file' => __FILE__, 'line' => __LINE__]);

                return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end handleMicroServices() at Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(data: $response, errMsg: $e->getMessage());
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

    private function handleMicroServicePostRequest($url, $data)
    {

        $this->logMe(message: 'start handleMicroServicePostRequest()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        $this->logMe(message: 'serviceOutput handleMicroServicePostRequest()', data: ['url' => $url]);
        $this->logMe(message: 'serviceOutput handleMicroServicePostRequest()', data: ['response' => $response]);
        $this->logMe(message: 'end handleMicroServicePostRequest()', data: ['file' => __FILE__, 'line' => __LINE__]);

        return json_decode($response);

    }

    public function sendOtpByMobile(Request $request)
    {
        $this->logMe(message: 'start sendOtpByMobile()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        /** Prepare model or table or DB Data */
        $data = $request->all();
        $data['website'] = $request->header('website');
        try {
            /* Create response data */

            /** Call DB operations */
            $dbStatus = null;
            //$response['data']= $data;
            if (array_key_exists('email', $data) && array_key_exists('mobile', $data)) {
                $response['statusCode'] = 404;
                $response['msg'] = 'Either Email or Mobile are accepted';
            } elseif (array_key_exists('email', $data)) {
                $response['msg'] = 'OTP Sent Successfully to your Email Id';
            } else {
                $dbStatus = $this->userRepository->sendOtpByMobile($data);
                $this->logMe(message: 'start sendOtpByMobile()', data: ['file' => $dbStatus]);
                if ($dbStatus['status']) {
                    $response['data'] = $dbStatus['data'];
                    $response['msg'] = 'OTP Sent Successfully to your Mobile Number';
                } else {
                    $response['msg'] = $dbStatus['data'];
                    $response['statusCode'] = 404;
                }
            }
            $this->logMe(message: 'end sendOtpByMobile()', data: ['file' => __FILE__, 'line' => __LINE__]);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            //return false;
            $this->logMe(message: 'end sendOtpByMobile() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function verifyOtp(Request $request)
    {
        $this->logMe(message: 'start verifyOtp()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        $data['website'] = $request->header('website');
        try {
            $dbStatus = $this->userRepository->verifyOtp($data);
            if ($dbStatus) {
                $response['statusCode'] = 200;
                $response['msg'] = 'OTP Verified Successfully';
                $response['data'] = $dbStatus;
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = 'Invalid OTP. Please try again';
                $response['data'] = [];
            }
            $this->logMe(message: 'end verifyOtp()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end verifyOtp() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getAadharUrl(Request $request)
    {
        $this->logMe(message: 'start getAadharUrl() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://live.meon.co.in/get_sso_route',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "company" : "viindhya",
                "workflowName" : "kuberascheme",
                "secret_key" : "k9txD5nWtwS6e38iiwDqL26Vb0vVi2iq",
                "notification" : true,
                "unique_keys" : {"referenceno" : '.$request->referenceno.'}
            }',
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
            ]);

            $res = curl_exec($curl);

            curl_close($curl);
            $response['data'] = json_decode($res);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getAadharUrl() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateBankDetails(Request $request)
    {
        $this->logMe(message: 'start updateBankDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->userRepository->updateBankDetails($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updateBankDetails() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updateBankDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateDeliveryAddress(Request $request)
    {
        $this->logMe(message: 'start updateDeliveryAddress() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->userRepository->updateDeliveryAddress($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updateDeliveryAddress() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updateDeliveryAddress() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function addContactMessages(Request $request)
    {
        /* Create response data */
        $this->logMe(message: 'start addContactMessages() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => '',
            'msg' => '',
            'statusCode' => 200,
        ];
        try {
            $response['data'] = $request->all();
            /** Call DB operations */
            if ($this->userRepository->addContactMessages($request->all())) {
                $response['statusCode'] = 200;
                $response['msg'] = 'Your details received successfully. Our team will contact you soon';
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = 'Something went wrong please try again';
            }
            /*send response data */
            $this->logMe(message: 'end addContactMessages() Service', data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end addContactMessages() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: '', errMsg: $e->getMessage());
        }
    }

    public function updateContactMessages(Request $request)
    {
        $this->logMe(message: 'start updateContactMessages() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        // $data['website']= $request->header('website');
        try {
            $dbStatus = $this->userRepository->updateContactMessages($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end updateContactMessages() Service', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end updateContactMessages() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function deleteUser(Request $request)
    {
        $this->logMe(message: 'start deleteUser()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = [
            'data' => [],
            'msg' => '',
            'statusCode' => 200,
        ];
        $data = $request->all();
        try {
            $dbStatus = $this->userRepository->deleteUser($data);
            if ($dbStatus['status']) {
                $response['statusCode'] = 200;
                $response['msg'] = $dbStatus['msg'];
            } else {
                $response['statusCode'] = 404;
                $response['msg'] = $dbStatus['msg'];
            }
            $this->logMe(message: 'end deleteUser()', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: json_encode($dbStatus), data: ['file' => __FILE__, 'line' => __LINE__]);

            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end deleteUser() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            $this->logMe(message: $e->getMessage(), data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }
    // private function
    public function getCompleteDetails(Request $request)
    {
        $this->logMe(message: 'start getCompleteDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);
        /* Create response data */
        $response = [
            'data' => [],
            'statusCode' => 200,
        ];
        $data = $request->all();

        try {
            $finalData=[
                'user' => '',
                'referrals' => [],
                'schemes' => [
                    'kubera' => [],
                    'digitalGold' => []
                ]
            ];
            if(array_key_exists('requestType',$data)){
                switch ($data['requestType']) {
                    case 'total':
                        /*User details*/
                        $finalData['user'] = $this->userRepository->findById($data['userId']);
                        /*User payments*/
                        $data['type']='getPaymentsByUserId';
                        $data['paymentFor']=config('app-constants.PAYMENT.TYPES.KUBERA');
                        $finalData['schemes']['kubera']=$this->userRepository->getCommonData($data);
                        $data['paymentFor']=config('app-constants.PAYMENT.TYPES.DIGITAL');
                        $finalData['schemes']['digitalGold']=$this->userRepository->getCommonData($data);
                        /*User referrals*/
                        $data['type']='getReferralsByUserId';
                        $finalData['referrals']=$this->userRepository->getCommonData($data);
                        break;
                    case 'uiReferrals':
                        $data['type']='uiReferrals';
                        $finalData=$this->userRepository->getCommonData($data);
                        break;
                    case 'uiBalance':
                        $data['type']='uiBalance';
                        $finalData=$this->userRepository->getCommonData($data);
                        break;
                    case 'user':
                        $finalData=$this->getUser();
                        break;
                    case 'uiSchemes':
                        $data['type']='uiSchemes';
                        $finalData=$this->userRepository->getCommonData($data);
                        break;
                }
            }



            $response['data'] = $finalData;
            $response['msg'] = config('app-constants.RESPONSE.MSG.GET_DATA_SUCCESSFUL');
            $this->logMe(message: 'end getCompleteDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);

            /*send response data */
            return $this->sendResponse($response['statusCode'], $response['msg'], $response['data'], '');
        } catch (\Exception $e) {
            $this->logMe(message: 'end getCompleteDetails() at Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(data: $response, errMsg: $e->getMessage());
        }
    }
}
