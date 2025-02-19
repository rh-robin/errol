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
        $data = Breed::with('characteristics')->get()->map(function ($breed) {
            $breed->characteristics->transform(function ($characteristic) {
                $characteristic->title = ucwords(str_replace('_', ' ', $characteristic->title));
                return $characteristic;
            });
            return $breed;
        });
        $message = '';
        return $this->sendResponse($data, $message, '', 200);
    }
}
