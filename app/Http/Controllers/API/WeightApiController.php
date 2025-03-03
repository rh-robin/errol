<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Weight;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeightApiController extends Controller
{
    use ResponseTrait;
    public function storeWeight(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(),[
            'pet_id' => 'required|exists:pets,id',
            'current_weight' => 'required|numeric',
            'weight_goal' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }
        //dd(today());

        // Get the validated data
        $validatedData = $validator->validated();


        try {
            $weight = Weight::where('pet_id', $validatedData['pet_id'])->whereDate('updated_at', today())->first();
            //dd($weight);
            if ($weight) {
                //dd($validatedData['current_weight']);
                $weight->current_weight =  $validatedData['current_weight'];
                $weight->weight_goal = $validatedData['weight_goal'];
                $weight->save();

            }else{
                $weight = new Weight();
                $weight->pet_id = $validatedData['pet_id'];
                $weight->current_weight =  $validatedData['current_weight'];
                $weight->weight_goal = $validatedData['weight_goal'];
                $weight->save();
            }

            $message = 'Weight data saved successfully.';

            return $this->sendResponse($weight, $message, '', 201);
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function getWeight($pet_id){
        $weight = Weight::where('pet_id', $pet_id)->get();

    }
}
