<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Traits\ResponseTrait;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PetApiController extends Controller
{
    use ResponseTrait;

    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:dog,cat',
            'd_o_b' => 'required|date',
            'gender' => 'required|in:male,female',
            //'age' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'weight_goal' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'additional_note' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            //return response()->json(['errors' => $validator->errors()], 422);
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            // Handle image upload
            $file = 'image';
            $imagePath = null;
            if ($request->hasFile($file)) {
                // Upload the new file
                $randomString = Str::random(10);
                $imagePath  = Helper::fileUpload($request->file($file), 'pet', $randomString);
            }

            // Create pet record
            $pet = Pet::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'category' => $request->category,
                'breed_id' => $request->breed_id,
                'd_o_b' => $request->d_o_b,
                'gender' => $request->gender,
                //'age' => $request->age,
                'weight' => $request->weight,
                'weight_goal' => $request->weight_goal,
                'height' => $request->height,
                'additional_note' => $request->additional_note,
                'image' => $imagePath,
            ]);

            DB::commit();

            $success = '';
            $message = 'Pet added successfully!';
            return $this->sendResponse($success, $message, '', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }


    public function update(Request $request, $id)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:dog,cat',
            'd_o_b' => 'required|date',
            'gender' => 'required|in:male,female',
            'weight' => 'nullable|numeric|min:0',
            'weight_goal' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'additional_note' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            // Find the pet by ID
            $pet = Pet::findOrFail($id);

            // Handle image upload if provided
            $file = 'image';
            $imagePath = $pet->image; // Keep the old image path by default
            if ($request->hasFile($file)) {
                if ($pet->image) {
                    Helper::fileDelete($pet->image);
                }
                // Upload the new file
                $randomString = Str::random(10);
                $imagePath  = Helper::fileUpload($request->file($file), 'pet', $randomString);
            }

            // Update pet record
            $pet->update([
                'name' => $request->name,
                'category' => $request->category,
                'breed_id' => $request->breed_id,
                'd_o_b' => $request->d_o_b,
                'gender' => $request->gender,
                'weight' => $request->weight,
                'weight_goal' => $request->weight_goal,
                'height' => $request->height,
                'additional_note' => $request->additional_note,
                'image' => $imagePath,
            ]);

            DB::commit();

            $success = '';
            $message = 'Pet updated successfully!';
            return $this->sendResponse($success, $message, '', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), [], 500);
        }
    }

}
