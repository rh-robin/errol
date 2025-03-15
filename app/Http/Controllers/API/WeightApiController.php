<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\FoodInfo;
use App\Models\Weight;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
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
        $today = now(); // Today’s date
        $sevenDaysAgo = $today->copy()->subDays(6); // Get date 6 days ago (total 7 days including today)

        // Get weight records for the last 7 days sorted by updated_at
        $weights = Weight::where('pet_id', $pet_id)
            ->whereBetween('updated_at', [$sevenDaysAgo, $today]) // Get data for the last 7 days
            ->orderBy('updated_at', 'asc')
            ->get(['current_weight', 'updated_at', 'weight_goal']);

        // If no data is found, return an empty response
        if ($weights->isEmpty()) {
            return $this->sendResponse(['weights' => [], 'weight_goal' => null], 'No weight data found.', '', 200);
        }

        $dailyData = [];
        $lastWeight = null; // Store the last known weight

        // Loop through the last 7 days
        for ($i = 0; $i < 7; $i++) {
            $date = $sevenDaysAgo->copy()->addDays($i); // Get the specific date

            // Get the last recorded weight up to this date
            $dayWeight = $weights->where('updated_at', '<=', $date->endOfDay())->last();

            // If no weight recorded on this date, use the last known weight
            if (!$dayWeight && $lastWeight) {
                $dayWeight = $lastWeight;
            }

            // Store the last known weight for fallback in the next iteration
            if ($dayWeight) {
                $lastWeight = $dayWeight;
                $dailyData[] = [
                    'current_weight' => $dayWeight->current_weight,
                    'date' => $date->format('M j'), // Format: "Mar 10"
                ];
            }
        }

        // Get the latest weight goal from the last record
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the response
        $response = [
            'weights' => $dailyData,
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

        $monthlyData = [];
        $lastWeight = null; // Store the last known weight

        // Get all months from 3 years ago until now
        $startPeriod = $threeYearsAgo->copy();
        $endDate = now()->endOfMonth(); // Up to the current month

        while ($startPeriod <= $endDate) {
            $endPeriod = $startPeriod->copy()->endOfMonth(); // End of the month

            // Get the last recorded weight in this month
            $monthWeight = $weights->whereBetween('updated_at', [$startPeriod, $endPeriod])->last();

            // If no weight data for this month, use the last known weight
            if (!$monthWeight && $lastWeight) {
                $monthWeight = $lastWeight;
            }

            // Store the last known weight for fallback in the next iteration
            if ($monthWeight) {
                $lastWeight = $monthWeight;
                $monthlyData[] = [
                    'current_weight' => $monthWeight->current_weight,
                    'month' => $startPeriod->format('M Y'), // Example: "Mar 2025"
                ];
            }

            // Move to the next month
            $startPeriod->addMonth()->startOfMonth();
        }

        // Get the latest weight goal
        $weightGoal = $weights->last()->weight_goal ?? null;

        // Prepare the response
        $response = [
            'weights' => $monthlyData,
            'weight_goal' => $weightGoal,
        ];

        return $this->sendResponse($response, 'Monthly weight data retrieved successfully.', '', 200);
    }




    /*========================== GET FOOD WEIGHT TODAY ========================*/
    public function foodWeightToday($pet_id)
    {
        $today = Carbon::today();

        $foods = FoodInfo::where('pet_id', $pet_id)
            ->whereDate('created_at', $today)
            ->get(['created_at', 'weight']);

        $response = $foods->map(function ($food) {
            return [
                'time' => $food->created_at->format('h:i A'), // Format time
                'weight' => $food->weight,
            ];
        });

        return $this->sendResponse($response, "Today's all food weight.", '', 200);
    }


    /*========================== GET FOOD WEIGHT BY DATE ========================*/
    public function foodWeightThisWeek($pet_id)
    {
        $startDate = Carbon::today()->subDays(6); // Start from 6 days ago
        $endDate = Carbon::today(); // Up to today

        // Fetch all food records in the last 7 days, including today
        $foodData = FoodInfo::where('pet_id', $pet_id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get()
            ->groupBy(function ($food) {
                return Carbon::parse($food->created_at)->format('Y-m-d'); // Group by exact date
            });

        // Prepare the response array for the last 7 days
        $response = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dayName = $date->format('l'); // Day name (Sunday, Monday, etc.)
            $dateString = $date->format('Y-m-d'); // Exact date in format Y-m-d

            // Calculate total weight for the day
            $totalWeight = isset($foodData[$dateString])
                ? $foodData[$dateString]->sum('weight')
                : 0; // If no data, set weight to 0

            $response[] = [
                'day' => $dayName,
                'weight' => $totalWeight,
            ];
        }

        return $this->sendResponse($response, "All food weight of this week.", null, 200);
    }



    /*========================== GET FOOD WEIGHT OF LAST FIVE MONTHS ========================*/
    public function foodWeightFiveMonths($pet_id)
    {
        $startDate = Carbon::today()->subMonths(4); // 5 months, including the current month
        $endDate = Carbon::today();

        // Fetch all food records for the last 5 months
        $foodData = FoodInfo::where('pet_id', $pet_id)
            ->whereBetween('created_at', [$startDate->startOfMonth(), $endDate->endOfMonth()])
            ->get()
            ->groupBy(function ($food) {
                return Carbon::parse($food->created_at)->format('Y-m'); // Group by month (Y-m format)
            });
        //dd($foodData);

        // Prepare the response array for the last 5 months
        $response = [];
        for ($i = 0; $i < 5; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $monthName = $date->format('F Y'); // Full month name with year (e.g., March 2025)
            $dateString = $date->format('Y-m'); // Exact month in format Y-m

            // Calculate total weight for the month
            $totalWeight = isset($foodData[$dateString])
                ? $foodData[$dateString]->sum('weight')
                : 0; // If no data, set weight to 0

            $response[] = [
                'month' => $monthName,
                'weight' => $totalWeight,
            ];
        }

        return $this->sendResponse($response, "All food weight of the last five months.", null, 200);
    }


}
