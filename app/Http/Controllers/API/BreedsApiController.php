<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Breed;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class BreedsApiController extends Controller
{
    use ResponseTrait;
    public function index(){
        $data = Breed::all();
        $message = '';
        return $this->sendResponse($data, $message, '', 200);
    }
}
