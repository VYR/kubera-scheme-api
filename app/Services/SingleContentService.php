<?php

namespace App\Services;

use App\Exceptions\GlobalException;
use App\GlobalResponseData;
use App\GlobalLogger;
use App\ServiceInterfaces\SingleContentInterface;
use App\RepositoryInterfaces\SingleContentRepositoryInterface;
use Illuminate\Http\Request;

class SingleContentService implements SingleContentInterface
{
    use GlobalResponseData;
    use GlobalLogger;
    protected SingleContentRepositoryInterface $singleContentRepositoryInterface;
    public function __construct(SingleContentRepositoryInterface $singleContentRepositoryInterface)
    {
        $this->singleContentRepositoryInterface = $singleContentRepositoryInterface;
    }
    public function addContent(Request $request)
    {
        // return $this->singleContentRepositoryInterface->addContent($request->all());
        $this->logMe(message:'start addContent()',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        /** Prepare model or table or DB Data */
        $data=$request->all();
        try{
            if($this->singleContentRepositoryInterface->addContent($data)){
                $response['statusCode']=200;
                $response['msg']= config('app-constants.RESPONSE.MSG.GENERAL.SUCCESS');
            }
            else {
                $response['statusCode']=404;
                $response['msg']= config('app-constants.RESPONSE.MSG.GENERAL.FAILED');
            }
            /*send response data */
            return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end addContent() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
    public function updateContent(Request $request)
    {
        
    }
    public function deleteContent(Request $request)
    {
        
    }
    public function getContent(Request $request)
    {
        $this->logMe(message:'start getContent()',data:['file' => __FILE__, 'line' => __LINE__]);
        $response=[
            'data' => [],
            'msg'=> '',
            'statusCode'=> 200
        ];
        /** Prepare model or table or DB Data */
        $data=$request->all();
        try{
            $response['data']=$this->singleContentRepositoryInterface->getContent($data);
            /*send response data */
            return $this->sendResponse($response['statusCode'],$response['msg'],$response['data'],'');
        }catch(\Exception $e){
            $this->logMe(message:'end getContent() Exception',data:['file' => __FILE__, 'line' => __LINE__]);
            throw new GlobalException(errCode:404,data:$data, errMsg: $e->getMessage());
        }
    }
}
