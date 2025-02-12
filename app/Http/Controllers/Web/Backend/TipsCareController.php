<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\TipsCare;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TipsCareController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = TipsCare::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    $url = $data->image ? asset($data->image) : asset('backend/images/image-not.png');
                    return '<img src="' . $url . '" alt="image" class="img-fluid" style="height:50px; width:50px">';
                })
                ->addColumn('content', function ($data) {
                    // Strip HTML tags and truncate the content
                    $content = strip_tags($data->content);
                    return $content;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                   <a href="' . route('admin.tips_care.edit', $data->id) . '" class="btn btn-primary text-white" title="View">
                       <i class="bx bxs-pencil"></i>
                   </a>
                   <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                       <i class="bx bxs-trash-alt"></i>
                   </a>
               </div>';
                })
                ->rawColumns(['action','content','image'])
                ->make();
        }
        return view('backend.layouts.tips_care.index');
    }


    public function create()
    {
        return view('backend.layouts.tips_care.create');
    }

    public function store(Request $request)
    {
        // ✅ Validate the incoming request
        $request->validate([
            'title'   => 'required|string|max:255',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg',
            'content' => 'required|string',
        ]);

        // 🗂️ Prepare data for insertion
        $data = [
            'title'   => $request->title,
            'content' => $request->content,
        ];

        try{
            // 📤 Handle image upload if present
            $file = 'image';
            if ($request->hasFile($file)) {
                // Upload the new file
                $randomString = Str::random(10);
                $data[$file]  = Helper::fileUpload($request->file($file), 'tips_care', $randomString);
            }

            // 💾 Save the data to the database
            TipsCare::create($data);

            // ✅ Redirect back with a success message
            return redirect()->route('admin.tips_care.index')->with('t-success', 'Tip & Care added successfully!');
        }catch(\Exception $e){
            return redirect()->route('admin.tips_care.index')->with('t-error', $e->getMessage());
        }
    }


    public function edit($id)
    {
        $data = TipsCare::findOrFail($id);
        return view('backend.layouts.tips_care.edit', compact('data'));
    }


    public function update(Request $request, $id)
    {
        // ✅ Validate the incoming request
        $request->validate([
            'title'   => 'required|string|max:255',
            'image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg',
            'content' => 'required|string',
        ]);

        // 🔍 Find the existing record
        $tipsCare = TipsCare::findOrFail($id);

        // 🗂️ Prepare data for update
        $data = [
            'title'   => $request->title,
            'content' => $request->content,
        ];

        try {
            // 📤 Handle image upload if present
            $file = 'image';
            if ($request->hasFile($file)) {
                // Delete the old image if exists
                if ($tipsCare->image && file_exists(public_path($tipsCare->image))) {
                    Helper::fileDelete($tipsCare->image);
                }

                // Upload the new file
                $randomString = Str::random(10);
                $data[$file]  = Helper::fileUpload($request->file($file), 'tips_care', $randomString);
            }

            // 💾 Update the data in the database
            $tipsCare->update($data);

            // ✅ Redirect back with a success message
            return redirect()->back()->with('t-success', 'Data updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }


    public function destroy($id)
    {
        try {
            // 🔍 Find the existing record
            $tipsCare = TipsCare::findOrFail($id);

            // 🗑️ Delete the associated image if it exists
            if ($tipsCare->image && file_exists(public_path($tipsCare->image))) {
                Helper::fileDelete($tipsCare->image);
            }

            // ❌ Delete the record from the database
            $tipsCare->delete();

            // ✅ Redirect back with a success message
            return response()->json(['success' => true, 'message' => 'Data deleted successfully.']);
        } catch (\Exception $e) {
            // ⚠️ Handle errors gracefully
            return response()->json(['errors' => true, 'message' => 'Data failed to delete']);
        }
    }


}
