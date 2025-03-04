<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\File;

use Intervention\Image\Laravel\Facades\Image as ImageIntervention;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $per_page = $request->per_page ?? 10;

        $query = Brand::query();

        if ($search !== null) {
            $query->where(function ($sub_query) use ($search) {
                $sub_query->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $search . '%')
                    ->orWhere('code', 'LIKE', '%' . $search . '%');
            });
        }

        $brand = $query
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($per_page);
        return response()->json($brand);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        try {
            // return $request->all();
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'title_kh' => 'required|string|max:255',
                'order_index' => 'nullable|string|max:255',
                'created_by' => 'nullable|string|max:255',
                'updated_by' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'code' => 'required|string|unique:brands,code',
            ]);
            // return $validated;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }


        if ($request->hasFile('image')) {
            $image_file = $request->file('image');
            $image_file_name = time() . '_' . $image_file->getClientOriginalName();

            // Define the path
            $file_path = public_path('images/brands/');
            $file_thumb_path = public_path('images/brands/thumb/');

            // Check if directory exists, if not, create it
            if (!File::exists($file_path)) {
                File::makeDirectory($file_path, 0755, true, true);
            }
            if (!File::exists($file_thumb_path)) {
                File::makeDirectory($file_thumb_path, 0755, true, true);
            }

            // Store Original Image
            $image_file->storeAs('images/brands', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/brands/thumb', $image_file_name, 'real_public');
                }
                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $createdItem = Brand::create($validated);
        return response()->json($createdItem, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        return response()->json($brand);
    }

    /**
     * Show the form for editing the specified resource.
     */

     public function update(Request $request, $id)
     {
         // return $request->all();
         $brand = Brand::find($id);

         if (empty($brand)) {
             return response()->json([
                 'message' => 'Resource not found.',
             ], 404);
         }

         try {
            // return $request->all();
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'title_kh' => 'required|string|max:255',
                'order_index' => 'nullable|string|max:255',
                'created_by' => 'nullable|string|max:255',
                'updated_by' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'code' => 'required|string|unique:brands,code',
            ]);
            // return $validated;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }


        if ($request->hasFile('image')) {
            $image_file = $request->file('image');
            $image_file_name = time() . '_' . $image_file->getClientOriginalName();

            // Define the path
            $file_path = public_path('images/brands/');
            $file_thumb_path = public_path('images/brands/thumb/');

            // Check if directory exists, if not, create it
            if (!File::exists($file_path)) {
                File::makeDirectory($file_path, 0755, true, true);
            }
            if (!File::exists($file_thumb_path)) {
                File::makeDirectory($file_thumb_path, 0755, true, true);
            }

            // Store Original Image
            $image_file->storeAs('images/brands', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/brands/thumb', $image_file_name, 'real_public');
                }
                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
         $brand->update($validated);

         return response()->json([
             'message' => 'brand updated successfully.',
             'brand' => $brand,
         ]);
     }

    public function edit(Brand $brand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        // Define the path
        $file_path = public_path('images/brands/');
        $file_thumb_path = public_path('images/brands/thumb/');

        if ($brand->image) {
            $old_image_path = $file_path . $brand->image;
            $old_thumb_path = $file_thumb_path . $brand->image;

            if (File::exists($old_image_path)) {
                File::delete($old_image_path);
            }
            if (File::exists($old_thumb_path)) {
                File::delete($old_thumb_path);
            }
        }

        $brand->delete();
        return response()->json([
            'message' => 'Delete Success',
        ], 200);

    }
}
