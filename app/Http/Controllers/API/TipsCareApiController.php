<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TipsCare;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class TipsCareApiController extends Controller
{
    use ResponseTrait;
    public function index(){
        $data = TipsCare::all();
        $message = '';
        return $this->sendResponse($data, $message, '', 200);
    }
}
