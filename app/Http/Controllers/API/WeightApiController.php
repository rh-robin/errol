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
            return $this->sendError($exception->getMessage(), [], $exception->getCode());
        }

    }

    /*public function getWeight($pet_id){
        $weights = Weight::where('pet_id', $pet_id)
            ->orderBy('updated_at', 'asc')
            ->get(['current_weight', 'updated_at', 'weight_goal']);


        $weightsArray = $weights->toArray();

        // Transform the data to rename and format `updated_at`
        $weightsArray = $weights->map(function ($weight) {
            return [
                'current_weight' => $weight->current_weight,
                'date' => $weight->updated_at->toDateString(), // Converts to YYYY-MM-DD format
            ];
        })->toArray();
        //dd($weights->last());

        // Get the last weight_goal value if there is at least one record
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the final response array
        $response = [
            'weights' => $weightsArray, // Array of current_weight and updated_at
            'weight_goal' => $weightGoal // Last weight goal value
        ];


        $message = 'Weight data saved successfully.';
        return $this->sendResponse($response, $message, '', 201);
    }*/

    public function getWeightByWeek($pet_id) {
        $startDate = now()->startOfWeek(); // Start of the current week
        $sixWeeksAgo = $startDate->copy()->subWeeks(5); // Get date 6 weeks ago

        // Get weight records for the last 6 weeks sorted by updated_at
        $weights = Weight::where('pet_id', $pet_id)
            ->where('updated_at', '>=', $sixWeeksAgo)
            ->orderBy('updated_at', 'asc')
            ->get(['current_weight', 'updated_at', 'weight_goal']);

        // If no data is found, return an empty response
        if ($weights->isEmpty()) {
            return $this->sendResponse(['weights' => [], 'weight_goal' => null], 'No weight data found.', '', 200);
        }

        $weeklyData = [];
        $lastWeight = null; // Store the last known weight

        // Loop through the last 6 weeks (every 7 days)
        for ($i = 0; $i < 6; $i++) {
            $weekDate = $sixWeeksAgo->copy()->addDays($i * 7); // Increment by 7 days

            // Get the last weight recorded up to this week
            $weekWeight = $weights->where('updated_at', '<=', $weekDate->endOfWeek())->last();

            // If no weight data for this week, use the last known weight
            if (!$weekWeight && $lastWeight) {
                $weekWeight = $lastWeight;
            }

            // Store the last known weight for fallback in the next iteration
            if ($weekWeight) {
                $lastWeight = $weekWeight;
                $weeklyData[] = [
                    'current_weight' => $weekWeight->current_weight,
                    'date' => $weekDate->format('M j'), // Format: "Mar 1"
                ];
            }
        }

        // Get the latest weight goal from the last record
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the response
        $response = [
            'weights' => $weeklyData,
            'weight_goal' => $weightGoal,
        ];

        return $this->sendResponse($response, 'Weight data retrieved successfully.', '', 200);
    }


    /*================= GET WEIGHT BY MONTH =================*/
    public function getWeightByMonth($pet_id) {
        $sixMonthsAgo = now()->subMonths(5)->startOfMonth(); // Get date 6 months ago

        // Get weight records for the last 6 months sorted by updated_at
        $weights = Weight::where('pet_id', $pet_id)
            ->where('updated_at', '>=', $sixMonthsAgo)
            ->orderBy('updated_at', 'asc')
            ->get(['current_weight', 'updated_at', 'weight_goal']);

        // If no data is found, return an empty response
        if ($weights->isEmpty()) {
            return $this->sendResponse(['weights' => [], 'weight_goal' => null], 'No weight data found.', '', 200);
        }

        $monthlyData = [];
        $lastWeight = null; // Store the last known weight

        // Loop through the last 6 months
        for ($i = 0; $i < 6; $i++) {
            $monthDate = $sixMonthsAgo->copy()->addMonths($i)->endOfMonth(); // Get end of each month

            // Get the last weight recorded up to this month
            $monthWeight = $weights->where('updated_at', '<=', $monthDate)->last();

            // If no weight data for this month, use the last known weight
            if (!$monthWeight && $lastWeight) {
                $monthWeight = $lastWeight;
            }

            // Store the last known weight for fallback in the next iteration
            if ($monthWeight) {
                $lastWeight = $monthWeight;
                $monthlyData[] = [
                    'current_weight' => $monthWeight->current_weight,
                    'month' => $monthDate->format('F'), // Format: "March", "April"
                ];
            }
        }

        // Get the latest weight goal from the last record
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the response
        $response = [
            'weights' => $monthlyData,
            'weight_goal' => $weightGoal,
        ];

        return $this->sendResponse($response, 'Month wise weight data retrieved successfully.', '', 200);
    }



    /*================== GE WEIGHT BY EVERY 6 MONTH =================*/
    public function getWeightBySixMonths($pet_id) {
        $threeYearsAgo = now()->subYears(3)->startOfMonth(); // Get date 3 years ago

        // Get weight records for the last 3 years sorted by updated_at
        $weights = Weight::where('pet_id', $pet_id)
            ->where('updated_at', '>=', $threeYearsAgo)
            ->orderBy('updated_at', 'asc')
            ->get(['current_weight', 'updated_at', 'weight_goal']);

        // If no data is found, return an empty response
        if ($weights->isEmpty()) {
            return $this->sendResponse(['weights' => [], 'weight_goal' => null], 'No weight data found.', '', 200);
        }

        $sixMonthData = [];
        $lastWeight = null; // Store the last known weight

        // Loop through the last 6-month periods (3 years / 6 months = 6 periods)
        for ($i = 0; $i < 6; $i++) {
            $startPeriod = $threeYearsAgo->copy()->addMonths($i * 6)->startOfMonth(); // Start of the period
            $endPeriod = $startPeriod->copy()->addMonths(5)->endOfMonth(); // End of the period

            // Get the last weight recorded up to this period
            $periodWeight = $weights->where('updated_at', '<=', $endPeriod)->last();

            // If no weight data for this period, use the last known weight
            if (!$periodWeight && $lastWeight) {
                $periodWeight = $lastWeight;
            }

            // Store the last known weight for fallback in the next iteration
            if ($periodWeight) {
                $lastWeight = $periodWeight;
                $sixMonthData[] = [
                    'current_weight' => $periodWeight->current_weight,
                    'period' => $startPeriod->format('M') . ' - ' . $endPeriod->format('M Y'), // Format: "Jan - Jun 2024"
                ];
            }
        }

        // Get the latest weight goal from the last record
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the response
        $response = [
            'weights' => $sixMonthData,
            'weight_goal' => $weightGoal,
        ];

        return $this->sendResponse($response, 'Six-month wise weight data retrieved successfully.', '', 200);
    }




}
