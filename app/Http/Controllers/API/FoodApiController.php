<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Traits\ResponseTrait;
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
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()], 422);
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }



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
                    "weight": "estimated weight of the food in grams",
                    "calorie": "estimated calorie content in kcal",
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
            return $this->sendError('Invalid response format from OpenAI', [], 500);
        }
        $message = 'food uploaded to chatgpt successfully!';
        return $this->sendResponse($cleanedNutritionInfo['EstimatedNutritionalInformation'], $message, '', 201);
    }


}
