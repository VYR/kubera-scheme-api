<?php

namespace App\Repositories;

use App\Exceptions\GlobalException;
use App\GlobalLogger;
use App\Interfaces\UserRepositoryInterface;
use App\Mail\EmailTemplate;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail as FacadesMail;

class UserRepository implements UserRepositoryInterface
{
    use GlobalLogger;

    public function __construct()
    {
        $this->logMe(isHeading: true, message: 'UserRepository', data: ['file' => __FILE__, 'line' => __LINE__]);
    }

    public function readDataParams($params = [])
    {
        $parameters = config('app-constants.paginationParams', []);
        foreach ($parameters as $key => $value) {
            if (array_key_exists($key, $params)) {
                if ($key === 'pageIndex') {
                    if ($params['pageIndex'] === 0) {
                        $parameters['pageIndex'] = 1;
                    } elseif ($params['pageIndex'] > 0) {
                        $parameters['pageIndex'] = $params['pageIndex'] + 1;
                    }
                } else {
                    $parameters[$key] = $params[$key];
                }
            }
        }

        return $parameters;
    }

    public function getEntireTableData($data = [])
    {
        $conditions = [];
        $directSearchKeys = ['email', 'created_at', 'updated_at'];
        $pagingParams = $this->readDataParams($data);
        if (array_key_exists('role', $data)) {
            array_push($conditions, ['user_details->signup_data->role', '=', strtoupper($data['role'])]);
        }
        $query = new User;
        if (array_key_exists('search', $data)) {
            $data['search'] = strtolower($data['search']);
            $query = $query->where('email', 'LIKE', '%'.$data['search'].'%');
            //$query->orWhere
            $query = $query->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.name")) LIKE ?', ['%'.$data['search'].'%'])
                ->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.email")) LIKE ?', ['%'.$data['search'].'%'])
                ->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.phoneNumber")) LIKE ?', ['%'.$data['search'].'%'])
                ->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.countryCode")) LIKE ?', ['%'.$data['search'].'%'])
                ->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.pan")) LIKE ?', ['%'.$data['search'].'%'])
                ->orWhereRaw('LOWER(json_extract(user_details, "$.signup_data.aadhar")) LIKE ?', ['%'.$data['search'].'%']);
            if (array_key_exists('role', $data)) {
                $query = $query->where('user_details->signup_data->role', '=', strtoupper($data['role']));
            }
            // ->orWhereRaw('LOWER(user_details->signup_data->email) LIKE ?',  ['%'.$data['search'].'%'])
            // ->orWhereRaw('LOWER(user_details->signup_data->phoneNumber) LIKE ?',  ['%'.$data['search'].'%'])
            // ->orWhereRaw('LOWER(user_details->signup_data->countryCode) LIKE ?',  ['%'.$data['search'].'%']);
        } elseif (count($conditions) > 0) {
            $query = $query->where($conditions);
        }
        if (in_array($pagingParams[config('app-constants.pagingKeys.sortKey')], $directSearchKeys)) {
            $query->orderBy($pagingParams[config('app-constants.pagingKeys.sortKey')], $pagingParams[config('app-constants.pagingKeys.sortDirection')]);
        } else {
            $query->orderBy('user_details->signup_data->'.$pagingParams[config('app-constants.pagingKeys.sortKey')], $pagingParams[config('app-constants.pagingKeys.sortDirection')]);
        }
        DB::listen(function ($query) {
            $this->logMe(message: 'end getEntireTableData()', data: [
                'file' => __FILE__,
                'line' => __LINE__,
                'query' => '['.date('Y-m-d H:i:s').']'.PHP_EOL.$query->sql.' ['.implode(', ', $query->bindings).']'.PHP_EOL.PHP_EOL,
            ]);

            //torage::append('logs/query.log', '[' . date('Y-m-d H:i:s') . ']' . PHP_EOL . $query->sql . ' [' . implode(', ', $query->bindings) . ']' . PHP_EOL . PHP_EOL);
        }
        );

        //$query =$query->orderByRaw('CAST(JSON_EXTRACT(user_details, "$.signup_data.'.$pagingParams[config('app-constants.pagingKeys.sortKey')].'") AS '.$pagingParams[config('app-constants.pagingKeys.sortKey')].')',$pagingParams[config('app-constants.pagingKeys.sortDirection')]);
        return $query->paginate($pagingParams[config('app-constants.pagingKeys.pageSize')],
            ['*'], 'users', $pagingParams[config('app-constants.pagingKeys.pageIndex')]);

    }

    public function getSettings($data = [])
    {

        return Setting::all();

    }

    public function findById($id)
    {
        return User::where('email', '=', $id)->first();
    }

    public function findByEmail($email) {}

    public function findByUsername($username) {}

    public function findByEmailAndPassword($email, $password) {}

    public function createUserByEmail(array $data) {}

    public function signup(array $data)
    {
        $resp = ['status' => false, 'data' => ''];
        $this->logMe(message: 'start signup()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            $conditions = [];
            if (array_key_exists('email', $data['user_details']['signup_data'])) {
                $conditions = [
                    ['user_details->signup_data->email', '=', $data['user_details']['signup_data']['email']],
                ];
                $response = User::where($conditions)->first();
                if (! is_null($response)) {
                    $resp['data'] = 'Email Already Existed';

                    return $resp;
                }
            } else {
                $resp['data'] = 'Email Required';

                return $resp;
            }
            if (array_key_exists('phoneNumber', $data['user_details']['signup_data']) &&
                array_key_exists('countryCode', $data['user_details']['signup_data'])
            ) {
                $conditions = [
                    ['user_details->signup_data->phoneNumber', '=', $data['user_details']['signup_data']['phoneNumber']],
                    ['user_details->signup_data->countryCode', '=', $data['user_details']['signup_data']['countryCode']],
                ];
                $response = User::where($conditions)->first();
                if (! is_null($response)) {
                    $resp['data'] = 'Phone Number with same country code ALready Existed';

                    return $resp;
                }
            } else {
                $resp['data'] = 'Phone Number and Country Code are mandatory';

                return $resp;
            }

            $user = new User;
            $user->fill($data);
            $user->user_details = $data['user_details'];
            $user->user_history = $data['user_details'];
            $resp['status'] = $user->save();
            if ($data['user_details']['signup_data']['role'] === 'SCHEME_MEMBER') {
                // $this->sendSignupMessage($data);
            }

            return $resp;

        } catch (\Exception $e) {
            $this->logMe(message: 'catch signup()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: [], errMsg: $e->getMessage());
        }
    }

    private function sendSignupMessage($existingRecord)
    {
        $this->logMe(message: 'start sendSignupMessage()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $data = [
            'salutation' => 'Dear '.$existingRecord['user_details']['signup_data']['name'],
            'subject' => 'Signup - '.$existingRecord['user_details']['signup_data']['name'],
            'body' => 'Thank you for signing up with VIINDHYA AU BULLION LLP! Get exclusive offers and updates on our premium gold and silver products.',
            'template' => 'otp',
            'to' => $existingRecord['user_details']['signup_data']['email'],
        ];
        $otpURL = 'https://360marketingservice.com/api/v2/SendSMS?SenderId=VIAUBU&Is_Unicode=false&Is_Flash=false&Message=Thank%20you%20for%20signing%20up%20with%20VIINDHYA%20AU%20BULLION%20LLP%21%20Get%20exclusive%20offers%20and%20updates%20on%20our%20premium%20gold%20and%20silver%20products.&MobileNumbers='.$existingRecord['user_details']['signup_data']['phoneNumber'].'&ApiKey=bdWWoqrc54f1Q5mvoD21eogUirIZHU%2Bl%2BzoPL2NVEd8%3D&ClientId=304b754c-ca9b-4028-b59f-1d8a08bffb4f';
        $this->sendSMS($otpURL);
        $this->sendEmail($data);
    }

    public function login(array $data)
    {
        $this->logMe(message: 'start login()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            $conditions1 = [
                ['user_details->signup_data->email', '=', $data['email']],
                ['user_details->signup_data->password', '=', $data['password']],
            ];
            $conditions2 = [
                ['user_details->signup_data->phoneNumber', '=', $data['email']],
                ['user_details->signup_data->password', '=', $data['password']],
            ];
            $response = User::where($conditions1)->orWhere($conditions2)->first();
            if (is_null($response)) {
                return $response;
            } else {
                return [
                    'user' => $response,
                    'token' => $response->createToken($response->email)->plainTextToken,
                ];
            }
        } catch (\Exception $e) {
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateUserDetails(array $data)
    {
        $this->logMe(message: 'start updateUserDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['email', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $existingRecord = $response->toArray();
                foreach ($data as $key => $value) {
                    if (array_key_exists($key, $existingRecord['user_details']['signup_data'])) {
                        $existingRecord['user_details']['signup_data'][$key] = $value;
                    } else {
                        $existingRecord['user_details']['signup_data'][$key] = $value;
                    }
                }
                $response->user_details = $existingRecord['user_details'];
                if ($response->save()) {
                    return [
                        'msg' => ' User Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update user',
                        'status' => false,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logMe(message: 'start updateUserDetails()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateSettings(array $data)
    {
        $this->logMe(message: 'start updateSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('setting_name', $data)) {
                return [
                    'msg' => ' Setting Name is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('setting_details', $data)) {
                return [
                    'msg' => ' Setting Details is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['setting_name', '=', $data['setting_name']],
            ];
            $response = Setting::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid Setting Name',
                    'status' => false,
                ];
            } else {
                $existingRecord = $response->toArray();
                foreach ($data['setting_details'] as $key => $value) {
                    if (array_key_exists($key, $existingRecord['setting_details'])) {
                        $existingRecord['setting_details'][$key] = $value;
                    } else {
                        $existingRecord['setting_details'][$key] = $value;
                    }
                }
                $response->setting_details = $existingRecord['setting_details'];
                if ($response->save()) {
                    return [
                        'msg' => ' Settings Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update Settings',
                        'status' => false,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logMe(message: 'start updateSettings()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function completeKyc(array $data)
    {
        $this->logMe(message: 'start completeKyc()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['email', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $existingRecord = $response->toArray();
                foreach ($data as $key => $value) {
                    $existingRecord['user_details']['signup_data'][$key] = $value;
                }
                $response->user_details = $existingRecord['user_details'];
                if ($response->save()) {
                    return [
                        'msg' => ' KYC Details Saved Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Save KYC Details ',
                        'status' => false,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logMe(message: 'start completeKyc()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function sendOtpByMobile(array $data)
    {
        $this->logMe(message: 'start sendOtpByMobile()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            $conditions = [
                ['user_details->signup_data->phoneNumber', '=', $data['mobile']],
                ['user_details->signup_data->countryCode', '=', $data['code']],
            ];
            $response = User::where($conditions)->first();
            $existingRecord = [];
            if ($response) {
                $existingRecord = $response->toArray();
            }
            if (is_null($response)) {
                return ['status' => false, 'data' => 'Invalid mobile number. Please try again with another number'];
            } elseif (array_key_exists('otp', $existingRecord['user_details'])) {
                $to_time = time();
                $from_time = $existingRecord['user_details']['otp']['date'];
                $timeDifference = round(abs($to_time - $from_time) / 60, 2);
                if ($existingRecord['user_details']['otp']['numOfTimes'] >= 2000) {
                    if ($timeDifference < 5) {
                        return ['status' => false, 'data' => 'Your OTP request limit 3 times exceeded.'];
                    } else {
                        return $this->sendMobileOtp($existingRecord, 0, $response);
                    }
                } else {
                    return $this->sendMobileOtp($existingRecord, ($existingRecord['user_details']['otp']['numOfTimes'] + 1), $response);
                }
            } else {
                return $this->sendMobileOtp($existingRecord, 0, $response);

            }

        } catch (\Exception $e) {
            $this->logMe(message: 'start sendOtpByMobile()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }

    }

    // private function generateOtp(){
    //     $otp=
    // }
    private function sendEmail($data)
    {
        $this->logMe(message: 'start sendEmail()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = ['status' => true, 'message' => 'sendEmail Process started'];
        $mailData = [
            'logo' => config('app-constants.IMAGES.LOGO'),
            'website' => config('app-constants.EMAILS.SITE_URL'),
            'team' => config('app-constants.EMAILS.TEAM'),
            'salutation' => $data['salutation'],
            'subject' => $data['subject'],
            'body' => $data['body'],
            'template' => $data['template'],
        ];
        $to = $data['to'];
        $resp = FacadesMail::to(config('app-constants.EMAILS.RAO'))->send(new EmailTemplate($mailData));
        $resp = FacadesMail::to($to)->send(new EmailTemplate($mailData));

        $this->logMe(message: 'end sendEmail()', data: ['file' => __FILE__, 'line' => __LINE__]);
        // return $response;
    }

    private function sendMobileOtp($existingRecord, $numOfTimes, $response)
    {
        $this->logMe(message: 'start sendMobileOtp()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $settings = Setting::first();
        $x = $settings->setting_details['otpNumbers'];
        $otpNum = mt_rand(111111, 999999);
        $isMatched = false;
        $cc = $existingRecord['user_details']['signup_data']['countryCode'];
        $mb = $existingRecord['user_details']['signup_data']['phoneNumber'];
        if(is_array($x))
        {

            foreach($x as $v)
            {
                if($v['countryCode'] == $cc && $v['phoneNumber'] == $mb)
                {
                    $isMatched = true;
                    $otpNum = $v['otp'];
                    break;
                }
            }
        }
        $otp = ['value' => $otpNum, 'numOfTimes' => $numOfTimes, 'date' => time()];
        $existingRecord['user_details']['otp'] = $otp;
        $response->user_details = $existingRecord['user_details'];
        if ($response->save()) {
            $data = [
                'salutation' => 'Dear '.$existingRecord['user_details']['signup_data']['name'],
                'subject' => 'Login OTP - '.$otpNum,
                'body' => 'Your OTP to login to Kubera Scheme is '.$otpNum,
                'template' => 'otp',
                'to' => $existingRecord['user_details']['signup_data']['email'],
            ];
           $otpURL = 'https://360marketingservice.com/api/v2/SendSMS?SenderId=VIAUBU&Is_Unicode=false&Is_Flash=false&Message='.$otpNum.'%20is%20your%20one-time%20password%20for%20your%20Kubera%20Account%20powered%20by%20%22VIINDHYA%20AU%20BULLION%20LLP%22.This%20OTP%20is%20valid%20only%20for%205%20minutes.&MobileNumbers='.$existingRecord['user_details']['signup_data']['phoneNumber'].'&ApiKey=bdWWoqrc54f1Q5mvoD21eogUirIZHU%2Bl%2BzoPL2NVEd8%3D&ClientId=304b754c-ca9b-4028-b59f-1d8a08bffb4f';
           if(!$isMatched)
           {
                $this->sendSMS($otpURL);
                $this->sendEmail($data);
           }


            return ['status' => true, 'data' => $existingRecord['user_details']['signup_data']['email']];
        } else {
            return ['status' => false, 'data' => ''];
        }
    }

    public function verifyOtp(array $data)
    {
        $this->logMe(message: 'start verifyOtp()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            $conditions1 = [
                ['user_details->signup_data->email', '=', $data['email']],
                ['user_details->signup_data->website', '=', $data['website']],
            ];
            $conditions2 = [
                ['user_details->signup_data->phoneNumber', '=', $data['email']],
                ['user_details->signup_data->website', '=', $data['website']],
            ];
            $response = User::where($conditions1)->orWhere($conditions2)->first();
            if (! array_key_exists('otp', $data)) {
                return null;
            }
            if (is_null($response)) {
                return $response;
            } else {
                $existingRecord = $response->toArray();
                if (array_key_exists('otp', $existingRecord['user_details'])) {
                    if (
                        $existingRecord['user_details']['otp']['value'] === $data['otp'] ||
                        in_array($existingRecord['user_details']['signup_data']['role'], ['DEVELOPER', 'ADMIN'])
                    ) {
                        return [
                            'user' => $response,
                            'token' => $response->createToken($response->email)->plainTextToken,
                        ];
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            }
        } catch (\Exception $e) {
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    private function sendSMS($url)
    {
        $this->logMe(message: 'start sendSMS()', data: ['file' => __FILE__, 'line' => __LINE__]);
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
        $this->logMe(message: 'serviceOutput sendSMS()', data: ['url' => $url]);
        $this->logMe(message: 'serviceOutput sendSMS()', data: ['response' => $response]);
        $this->logMe(message: 'end sendSMS()', data: ['file' => __FILE__, 'line' => __LINE__]);
        // return json_decode($response);

    }

    public function updateBankDetails(array $data)
    {
        $this->logMe(message: 'start updateBankDetails() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('bank_details', $data)) {
                return [
                    'msg' => ' Bank details key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['id', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $response->bank_details = $data['bank_details'];
                if ($response->save()) {
                    return [
                        'msg' => ' Bank Details Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update Bank Details',
                        'status' => false,
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end updateBankDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateDeliveryAddress(array $data)
    {
        $this->logMe(message: 'start updateDeliveryAddress() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('delivery_address', $data)) {
                return [
                    'msg' => ' Delivery Address key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['id', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $response->delivery_address = $data['delivery_address'];
                if ($response->save()) {
                    return [
                        'msg' => ' Delivery Address Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update Delivery Address',
                        'status' => false,
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end updateDeliveryAddress() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updatePaymentDetails(array $data)
    {
        $this->logMe(message: 'start updatePaymentDetails() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('payment_details', $data)) {
                return [
                    'msg' => ' Payment details key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['id', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $payment = new Payment;
                $payment->userId = $data['userId'];
                $payment->payment_details = $data['payment_details'];
                if ($payment->save()) {
                    return [
                        'msg' => ' Payment Details Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update Payment Details',
                        'status' => false,
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end updatePaymentDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function addPaymentDetails(array $data)
    {
        $this->logMe(message: 'start addPaymentDetails() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('payment_details', $data)) {
                return [
                    'msg' => ' Payment details key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['id', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $payment = new Payment;
                $payment->userId = $data['userId'];
                $payment->payment_details = $data['payment_details'];
                if ($payment->save()) {
                    return [
                        'msg' => ' Payment Completed Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Complete Payment',
                        'status' => false,
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end addPaymentDetails() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function addContactMessages(array $data)
    {
        $this->logMe(message: 'start addContactMessages() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            $obj = new ContactMessage;
            $obj->fill($data);
            $obj->employee_reply = 'Just received message';
            $obj->status = 'SUBMITTED';
            $obj->contact_message_history = [$data];
            $this->logMe(message: 'end addContactMessages() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);

            return $obj->save();
        } catch (\Exception $e) {
            $this->logMe(message: 'end addContactMessages() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function updateContactMessages(array $data)
    {
        $this->logMe(message: 'start updateDeliveryAddress() Repository', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            if (! array_key_exists('delivery_address', $data)) {
                return [
                    'msg' => ' Delivery Address key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['id', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                $response->delivery_address = $data['delivery_address'];
                if ($response->save()) {
                    return [
                        'msg' => ' Delivery Address Updated Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Update Delivery Address',
                        'status' => false,
                    ];
                }
            }

        } catch (\Exception $e) {
            $this->logMe(message: 'end updateDeliveryAddress() Exception', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function deleteUser(array $data)
    {
        $this->logMe(message: 'start deleteUser()', data: ['file' => __FILE__, 'line' => __LINE__]);
        try {
            if (! array_key_exists('userId', $data)) {
                return [
                    'msg' => ' User Id key is mandatory',
                    'status' => false,
                ];
            }
            $conditions = [
                ['email', '=', $data['userId']],
            ];
            $response = User::where($conditions)->first();
            if (is_null($response)) {
                return [
                    'msg' => 'Invalid User',
                    'status' => false,
                ];
            } else {
                if ($response->delete()) {
                    return [
                        'msg' => ' User Deleted Successfully',
                        'status' => true,
                    ];
                } else {
                    return [
                        'msg' => 'Unable to Delete user',
                        'status' => false,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logMe(message: 'start deleteUser()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getCommonData(array $data)
    {
        $this->logMe(message: 'start getCommonData()', data: ['file' => __FILE__, 'line' => __LINE__]);
        $response = '';
        try {
            switch ($data['type']) {
                case 'getPaymentsByUserId':
                    $response = $this->getPaymentsByUserId($data);
                    break;
                case 'getReferralsByUserId':
                    $response = $this->getTotalAmountByUserId($data);
                    break;
                case 'uiReferrals':
                    $response = $this->getuiReferrals($data);
                    break;
                case 'uiBalance':
                    $response = $this->getuiBalance($data);
                    break;
                case 'uiSchemes':
                    $response = $this->getuiSchemes($data);
                    break;

            }

            return $response;
        } catch (\Exception $e) {
            $this->logMe(message: 'Exception getCommonData()', data: ['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode: 404, data: $data, errMsg: $e->getMessage());
        }
    }

    public function getPaymentsByUserId($data)
    {
        return Payment::where([
            ['userId', '=', $data['userId']],
            ['payment_details->paymentFor', '=', strtoupper($data['paymentFor'])],
        ])->get();
    }

    public function getReferralsByUserId($data)
    {
        return User::select('email')->where([
            ['user_details->signup_data->referralCode', '=', $data['userId']],
        ])->get();
    }

    public function getReferralPaymentsByUserId($data)
    {
        return Payment::select(
            'userId',
            DB::raw('json_extract(payment_details, "$.amount_paid") as amount'),
            DB::raw('json_extract(payment_details, "$.paymentFor") as paidFor')
        )->whereIn('userId', User::select('email')->where([
            ['user_details->signup_data->referralCode', '=', $data['userId']],
        ]))->get();
    }

    public function getReferralAmountsByUserId($data)
    {
        return Payment::select(
            'userId',
            DB::raw('json_extract(payment_details, "$.amount_paid") as amount'),
            DB::raw('json_extract(payment_details, "$.paymentFor") as paidFor')
        )->whereIn('userId', User::select('email')->where([
            ['user_details->signup_data->referralCode', '=', $data['userId']],
        ]))->get();
    }

    public function getTotalAmountByUserId($data)
    {
        return Payment::select(
            DB::raw('SUM(json_extract(payment_details, "$.amount_paid")) as amount')
        )->whereIn('userId', User::select('email')->where([
            ['user_details->signup_data->referralCode', '=', $data['userId']],
        ]))->get();
    }

    public function getuiReferrals($data)
    {
        $referredUsers = User::select(
            'created_at',
            'email as userId',
            DB::raw('JSON_UNQUOTE(json_extract(user_details, "$.signup_data.name")) as name'),
            DB::raw('JSON_UNQUOTE(json_extract(user_details, "$.signup_data.email")) as email'),
            DB::raw('JSON_UNQUOTE(json_extract(user_details, "$.signup_data.phoneNumber")) as phoneNumber'),
            DB::raw('JSON_UNQUOTE(json_extract(user_details, "$.signup_data.countryCode")) as countryCode')
        )->where([
            ['user_details->signup_data->referralCode', '=', $data['userId']],
        ])->get();
        $payments = Payment::select(
            'userId',
            DB::raw('json_extract(payment_details, "$.amount_paid") as amount'),
            DB::raw('json_extract(payment_details, "$.paymentFor") as paidFor'))
            ->whereIn('userId', User::select('email')->where([
                ['user_details->signup_data->referralCode', '=', $data['userId']]]))
            ->whereIn('payment_details->paymentFor', [
                config('app-constants.PAYMENT.TYPES.KUBERA'),
                config('app-constants.PAYMENT.TYPES.DIGITAL'),
            ])->get();

        $referralPayout = Payment::select(
            DB::raw('SUM(json_extract(payment_details, "$.amount_paid")) as amount')
        )
            ->where('userId', '=', $data['userId'])
            ->where('payment_details->paymentFor', '=', config('app-constants.PAYMENT.TYPES.PAYOUT')
            )->get();
        $settings = Setting::first();
        $referralAmount = 0;
        foreach ($payments as $value) {
            $referralAmount = $referralAmount + intval($value['amount']);
        }
        $unpaidUsers = [];
        $paidUsers = [];
        $paidUserIds = [];
        foreach ($payments as $value) {
            array_push($paidUserIds, $value['userId']);
        }
        foreach ($referredUsers as $value) {
            if (in_array($value['userId'], $paidUserIds)) {
                array_push($paidUsers, $value);
            } else {
                array_push($unpaidUsers, $value);
            }
        }
        $x = (count($referralPayout) > 0) ? $referralPayout[0]['amount'] : 0;
        $eligibleAmount = 0;
        if ($settings) {
            if ($referralAmount > intval($settings->setting_details['referralPayout']['type1']['min'])
            && $referralAmount < intval($settings->setting_details['referralPayout']['type1']['min'])
            ) {
                $eligibleAmount = $referralAmount * ($settings->setting_details['referralPayout']['type1']['rate'] / 100);
            } else {
                $eligibleAmount = $referralAmount * ($settings->setting_details['referralPayout']['type2']['rate'] / 100);
            }
        }

        return [
            'total' => count($referredUsers),
            'paidUsers' => $paidUsers,
            'unpaidUsers' => $unpaidUsers,
            'payoutAmount' => (count($referralPayout) > 0) ? $referralPayout[0]['amount'] : 0,
            'totalReferralAmount' => $referralAmount,
            'eligibileAmount' => $eligibleAmount,
            'settings' => $settings->setting_details['referralPayout'],

        ];
    }

    public function getuiBalance($data)
    {
        $spentMoney = Payment::select(
            DB::raw('SUM(json_extract(payment_details, "$.amount_paid")) as amount')
        )
            ->where('userId', '=', $data['userId'])
            ->whereIn('payment_details->paymentFor', [
                config('app-constants.PAYMENT.TYPES.KUBERA'),
                config('app-constants.PAYMENT.TYPES.DIGITAL'),
            ])->get();

        $addedMoney = Payment::select(
            DB::raw('SUM(json_extract(payment_details, "$.amount_paid")) as amount')
        )
            ->where('userId', '=', $data['userId'])
            ->where('payment_details->paymentFor', '=', config('app-constants.PAYMENT.TYPES.BALANCE')
            )->get();
        $unpaidUsers = [];
        $paidUsers = [];
        $paidUserIds = [];

        return [
            'added' => (count($addedMoney) > 0) ? $addedMoney[0]['amount'] : 0,
            'spent' => (count($spentMoney) > 0) ? $spentMoney[0]['amount'] : 0,
        ];
    }

    public function getPayments($data, $type, $isComplete)
    {
        $query = Payment::where(
            'userId', '=', $data['userId']
        )
            ->where('payment_details->paymentFor', '=', $type);
        if ($isComplete === 'YES') {
            $query = $query->where('payment_details->isCompleted', '=', 'YES');
        }

        return $query->get();
    }

    public function getuiSchemes($data)
    {
        return [
            'kubera' => [
                'completed' => $this->getPayments(
                    $data,
                    config('app-constants.PAYMENT.TYPES.KUBERA'),
                    'YES'
                ),
                'inComplete' => $this->getPayments(
                    $data,
                    config('app-constants.PAYMENT.TYPES.KUBERA'),
                    'NO'
                ),
            ],
            'digital' => [
                'completed' => $this->getPayments(
                    $data,
                    config('app-constants.PAYMENT.TYPES.DIGITAL'),
                    'YES'
                ),
                'inComplete' => $this->getPayments(
                    $data,
                    config('app-constants.PAYMENT.TYPES.DIGITAL'),
                    'NO'
                ),
            ],
        ];
    }
}