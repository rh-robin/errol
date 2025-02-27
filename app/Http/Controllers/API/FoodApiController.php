<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\FoodInfo;
use App\Models\Pet;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FoodApiController extends Controller
{
    use ResponseTrait;
    public function analyzeFood(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(),[
            'image' => 'required|image|mimes:jpg,jpeg,png,gif',
            'pet_id' => 'required|integer|exists:pets,id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        //$pet = Pet::findOrFail($request->pet_id);


        // Get image file
        $image = $request->file('image');

        // Convert image to base64
        $imageBase64 = base64_encode(file_get_contents($image->getRealPath()));

        // Send a request to the OpenAI API
        $apiKey = config('services.openai.api_key');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert nutritionist and food analyst.'],
                ['role' => 'user', 'content' => [
                    ['type' => 'text', 'text' => 'Analyze this food image and return the following details in JSON format under the variable "EstimatedNutritionalInformation":'],
                    ['type' => 'text', 'text' => '{
                    "isFood": "yes or no",
                    "name": "just name of the food without any pets or other words",
                    "weight": "estimated weight of the food in grams",
                    "calorie": "estimated calorie content in kcal",
                    "exercise_time": "estimated exercise_time in minutes to burn the calories",
                    "protein": "estimated protein content in grams",
                    "carbs": "estimated carbohydrate content in grams",
                    "fat": "estimated fat content in grams"
                }'],
                    ['type' => 'image_url', 'image_url' => ['url' => "data:image/{$image->getClientOriginalExtension()};base64," . $imageBase64]]
                ]],
            ],
            'max_tokens' => 300
        ]);

        // Return the response
        // Parse and clean the response
        $nutritionInfo = $response->json('choices.0.message.content');

        // Remove the markdown formatting (```json\n and \n```), then decode the JSON
        $cleanedNutritionInfo = json_decode(trim(str_replace(["```json\n", "\n```"], '', $nutritionInfo)), true);

        // Check if the data is valid
        if (!isset($cleanedNutritionInfo['EstimatedNutritionalInformation'])) {
            return $this->sendError('Invalid response format from AI', [], 500);
        }else{
            $foodInfo = new FoodInfo();
            $foodInfo->pet_id = $request->pet_id;
            $foodInfo->name = $cleanedNutritionInfo['EstimatedNutritionalInformation']['name'];
            $foodInfo->weight = $cleanedNutritionInfo['EstimatedNutritionalInformation']['weight'];
            $foodInfo->calorie = $cleanedNutritionInfo['EstimatedNutritionalInformation']['calorie'];
            $foodInfo->exercise_time = $cleanedNutritionInfo['EstimatedNutritionalInformation']['exercise_time'];
            $foodInfo->protein = $cleanedNutritionInfo['EstimatedNutritionalInformation']['protein'];
            $foodInfo->carbs = $cleanedNutritionInfo['EstimatedNutritionalInformation']['carbs'];
            $foodInfo->fat = $cleanedNutritionInfo['EstimatedNutritionalInformation']['fat'];
            $file = 'image';
            if ($request->hasFile($file)) {
                // Upload the new file
                $randomString = Str::random(10);
                $foodInfo->image  = Helper::fileUpload($request->file($file), 'food', $randomString);
            }
            $foodInfo->save();
        }
        $message = 'food uploaded to chatgpt successfully!';
        return $this->sendResponse($cleanedNutritionInfo['EstimatedNutritionalInformation'], $message, '', 201);
    }


    public function getFoodInfoByDate(Request $request){
        // Validate the request
        $validator = Validator::make($request->all(),[
            'date' => 'required|date_format:d/m/Y',
            'pet_id' => 'required|integer|exists:pets,id'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }
        $date = Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');

        $data = FoodInfo::where('pet_id', $request->pet_id)->whereDate('created_at', $date)->get();

        // Calculate total values
        $total_calorie = $data->sum('calorie');
        $total_protein = $data->sum('protein');
        $total_carbs = $data->sum('carbs');
        $total_fat = $data->sum('fat');

        // Add totals to response
        $response = [
            'food_data' => $data,
            'total_calorie' => $total_calorie,
            'total_protein' => $total_protein,
            'total_carbs' => $total_carbs,
            'total_fat' => $total_fat,
        ];

        $message = 'food data for date: '.$date.'.';
        return $this->sendResponse($response, $message, '', 200);
    }





}
