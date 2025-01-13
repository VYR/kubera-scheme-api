<?php

namespace App\Services;
use App\Exceptions\GlobalException;
use App\Exceptions\GlobalExceptionHandler;
use App\GlobalLogger;
use App\RepositoryInterfaces\SingleContentRepositoryInterface;
use App\Models\User;
use App\Models\SingleContent;
use Illuminate\Support\Arr;

class SingleContentRepositoryService implements SingleContentRepositoryInterface
{
    use GlobalLogger;
    public function __construct()
    {
        //
    }
    public function addContent(array $data){

        $this->logMe(message:'start addContent()',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            $allowedColumns=[
                'main_page',
                'products',
                'credit_assessment',
                'blogs',
                'webinars',
                'faqs',
                'about_us',
                'careers',
                'contact_us',
                'refer_earn',
                'become_partner',
                'after_login',
                'consultation',
                'complete_kyc',
                'schemes',
                'scheme_details1',
                'scheme_details2',
                'dashboard',
                'profile'
            ];
            $dataWithColumns=[];
            $errMsg="";
            $dbStatus=0;
            if(!is_array($data)){
                $errMsg="Invalid Data Received";
            }
            else if(!count($data)) {
                $errMsg="No Data Received";
            }
            else{
                foreach ($allowedColumns as $column) {
                    if(array_key_exists($column, $data)){
                        $dataWithColumns[$column]=$data[$column];
                    }    
                }
                if(!count($dataWithColumns)){
                    $errMsg="Invalid Columns Received";
                }
                else{
                    $singleContent = SingleContent::find(1);
                    $singleContent->fill($dataWithColumns);
                    $dbStatus = $singleContent->save();
                }
            }
            if($dbStatus){
                return $dbStatus;
            }
            else{
                throw new GlobalException(errCode:404,data:$data, errMsg: $errMsg);
            }
        }catch(\Exception $e){
            $this->logMe(message:'end addContent() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }

    public function updateContent(array $data){

    }
    public function deleteContent(array $data){

    }
    public function getContent(array $data){
        $this->logMe(message:'start getContent()',data:['file' => __FILE__, 'line' => __LINE__]);
        try{
            $dbStatus = SingleContent::find(1)->toArray();
            if(array_key_exists('key', $data)){
                return $dbStatus[$data['key']];
            } 
            else{
                return $dbStatus;
            }
            
            
        }catch(\Exception $e){
            $this->logMe(message:'end getContent() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:[], errMsg: $e->getMessage());
        }
    
    }
}
