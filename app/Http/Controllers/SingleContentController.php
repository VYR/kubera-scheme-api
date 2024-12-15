<?php

namespace App\Http\Controllers;

use App\ServiceInterfaces\SingleContentInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class SingleContentController extends Controller
{
    protected SingleContentInterface $singleContentInterface;
    public function __construct(SingleContentInterface $singleContentInterface) {
        $this->singleContentInterface = $singleContentInterface;
    }
    public function addSingleContent(Request $request){
        return $this->singleContentInterface->addContent($request);
    }
    public function getSingleContent(Request $request){
        return $this->singleContentInterface->getContent($request);
    }
}
