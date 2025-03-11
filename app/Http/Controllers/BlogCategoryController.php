<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Http\Requests\UpdateBlogCategoryRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\File;

use Intervention\Image\Laravel\Facades\Image as ImageIntervention;

class BlogCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? null;
        $status = $request->status ?? null;
        $sort_by = $request->sort_by ?? null;
        $per_page = $request->per_page ?? 10;
        $parent_code = $request->parent_code ?? null;
        $main_category = $request->main_category ?? '0';

        $query = BlogCategory::query();

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




        $blog_categories = $query
            ->orderBy('order_index', 'asc')
            ->orderBy('id', 'desc')
            ->paginate($per_page);
        return response()->json($blog_categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
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
                 'parent_code' => 'nullable|string|max:255|exists:blog_categories,code',
                 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                 'code' => 'required|string|unique:blog_categories,code',
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
             $file_path = public_path('images/blog_categories/');
             $file_thumb_path = public_path('images/blog_categories/thumb/');

             // Check if directory exists, if not, create it
             if (!File::exists($file_path)) {
                 File::makeDirectory($file_path, 0755, true, true);
             }
             if (!File::exists($file_thumb_path)) {
                 File::makeDirectory($file_thumb_path, 0755, true, true);
             }

             // Store Original Image
             $image_file->storeAs('images/blog_categories', $image_file_name, 'real_public');

             // Resize and save the image
             try {
                 $image = ImageIntervention::read($image_file);
                 $original_width = $image->width();
                 if ($original_width > 650) {
                     $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                 } else {
                     $image_file->storeAs('images/blog_categories/thumb', $image_file_name, 'real_public');
                 }
                 $validated['image'] = $image_file_name;
             } catch (\Exception $e) {
                 return response()->json([
                     'message' => 'Image processing failed.',
                     'error' => $e->getMessage(),
                 ], 500);
             }
         }

         $createdItem = BlogCategory::create($validated);
         return response()->json($createdItem, 200);
     }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog_category = BlogCategory::find($id);

        if (empty($blog_category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        return response()->json($blog_category);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogCategory $blogCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // return $request->all();
        $blog_category = BlogCategory::find($id);

        if (empty($blog_category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                 'title_kh' => 'required|string|max:255',
                 'order_index' => 'nullable|string|max:255',
                 'created_by' => 'nullable|string|max:255',
                 'updated_by' => 'nullable|string|max:255',
                 'parent_code' => 'nullable|string|max:255|exists:blog_categories,code',
                 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                 'code' => 'required|string|unique:blog_categories,code, ' . $id,
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
            $file_path = public_path('images/blog_categories/');
            $file_thumb_path = public_path('images/blog_categories/thumb/');

            // Check if directory exists, if not, create it
            if (!File::exists($file_path)) {
                File::makeDirectory($file_path, 0755, true, true);
            }
            if (!File::exists($file_thumb_path)) {
                File::makeDirectory($file_thumb_path, 0755, true, true);
            }

            // Delete the old image and thumbnail if they exist
            if ($blog_category->image) {
                $old_image_path = $file_path . $blog_category->image;
                $old_thumb_path = $file_thumb_path . $blog_category->image;

                if (File::exists($old_image_path)) {
                    File::delete($old_image_path);
                }
                if (File::exists($old_thumb_path)) {
                    File::delete($old_thumb_path);
                }
            }

            // Store Original Image
            $image_file->storeAs('images/blog_categories', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/blog_categories/thumb', $image_file_name, 'real_public');
                }
                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            $validated['image'] = $blog_category->image;
        }

        $blog_category->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $blog_category,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        // Validate the request
        $validated = $request->validate([
            'status' => 'required|boolean', // Adjust validation as needed
        ]);

        // Find the category
        $blog_category = BlogCategory::find($id);

        // Handle case when category is not found
        if (!$blog_category) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        // Update status
        $blog_category->update(['status' => $validated['status']]);

        // Return updated category
        return response()->json([
            'message' => 'Status updated successfully.',
            'category' => $blog_category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = BlogCategory::find($id);

        if (empty($category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        // Define the path
        $file_path = public_path('images/blog_categories/');
        $file_thumb_path = public_path('images/blog_categories/thumb/');

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

        if (count($category->sub_blog_categories) > 0) {
            foreach ($category->sub_blog_categories as $key => $child) {
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
