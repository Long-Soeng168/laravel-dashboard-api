<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image as ImageIntervention;

use function Laravel\Prompts\search;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $status = $request->status ?? null;
        $sort_by = $request->sort_by ?? null;
        $per_page = $request->per_page ?? 10;
        $parent_code = $request->parent_code ?? null;
        $main_category = $request->main_category ?? '0';

        $query = Category::query();

        if ($search !== null) {
            $query->where(function ($sub_query) use ($search) {
                $sub_query->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('title_kh', 'LIKE', '%' . $search . '%')
                    ->orWhere('code', 'LIKE', '%' . $search . '%');
            });
        }

        if ($status != null) {
            $query->where('status', $status);
        }

        if ($parent_code != null) {
            $query->where('parent_code', $parent_code);
        }

        if ($main_category == '1') {
            $query->where('parent_code', null);
        }


        switch ($sort_by) {
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'title_asc';
                $query->orderBy('title', 'asc');
                break;
            case 'order_index_desc';
                $query->orderBy('order_index', 'desc');
                break;
            case 'order_index_asc';
                $query->orderBy('order_index', 'asc');
                break;
        }




        $categories = $query
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($per_page);
        return response()->json($categories);
    }

    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'title_kh' => 'required|string|max:255',
                'order_index' => 'nullable|string|max:255',
                'parent_code' => 'nullable|string|max:255|exists:categories,code',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'code' => 'required|string|unique:categories,code',
            ]);
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
            $file_path = public_path('images/categories/');
            $file_thumb_path = public_path('images/categories/thumb/');

            // Check if directory exists, if not, create it
            if (!File::exists($file_path)) {
                File::makeDirectory($file_path, 0755, true, true);
            }
            if (!File::exists($file_thumb_path)) {
                File::makeDirectory($file_thumb_path, 0755, true, true);
            }

            // Store Original Image
            $image_file->storeAs('images/categories', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/categories/thumb', $image_file_name, 'real_public');
                }
                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $createdItem = Category::create($validated);
        return response()->json($createdItem, 200);
    }

    public function show($id)
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        // return $request->all();
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'title_kh' => 'required|string|max:255',
                'order_index' => 'nullable|string|max:255',
                'parent_code' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'code' => 'required|string|unique:categories,code, ' . $id,
            ]);
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
            $file_path = public_path('images/categories/');
            $file_thumb_path = public_path('images/categories/thumb/');

            // Check if directory exists, if not, create it
            if (!File::exists($file_path)) {
                File::makeDirectory($file_path, 0755, true, true);
            }
            if (!File::exists($file_thumb_path)) {
                File::makeDirectory($file_thumb_path, 0755, true, true);
            }

            // Delete the old image and thumbnail if they exist
            if ($category->image) {
                $old_image_path = $file_path . $category->image;
                $old_thumb_path = $file_thumb_path . $category->image;

                if (File::exists($old_image_path)) {
                    File::delete($old_image_path);
                }
                if (File::exists($old_thumb_path)) {
                    File::delete($old_thumb_path);
                }
            }

            // Store Original Image
            $image_file->storeAs('images/categories', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/categories/thumb', $image_file_name, 'real_public');
                }
                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            $validated['image'] = $category->image;
        }

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $category,
        ]);
    }
    public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'status' => 'required|boolean', // Adjust validation as needed
        ]);

        // Find the category
        $category = Category::find($id);

        // Handle case when category is not found
        if (!$category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        // Update status
        $category->update(['status' => $validated['status']]);

        // Return updated category
        return response()->json([
            'message' => 'Status updated successfully.',
            'category' => $category,
        ]);
    }


    public function destroy($id)
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        // Define the path
        $file_path = public_path('images/categories/');
        $file_thumb_path = public_path('images/categories/thumb/');

        if ($category->image) {
            $old_image_path = $file_path . $category->image;
            $old_thumb_path = $file_thumb_path . $category->image;

            if (File::exists($old_image_path)) {
                File::delete($old_image_path);
            }
            if (File::exists($old_thumb_path)) {
                File::delete($old_thumb_path);
            }
        }

        if (count($category->children) > 0) {
            foreach ($category->children as $key => $child) {
                if ($child->image) {
                    $old_image_path = $file_path . $child->image;
                    $old_thumb_path = $file_thumb_path . $child->image;

                    if (File::exists($old_image_path)) {
                        File::delete($old_image_path);
                    }
                    if (File::exists($old_thumb_path)) {
                        File::delete($old_thumb_path);
                    }
                }
            }
        }

        $category->delete();
        return response()->json([
            'message' => 'Delete Success',
        ], 200);
    }
}
