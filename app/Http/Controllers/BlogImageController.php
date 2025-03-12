<?php

namespace App\Http\Controllers;

use App\Models\BlogImage;
use App\Http\Requests\UpdateBlogImageRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\File;

use Intervention\Image\Laravel\Facades\Image as ImageIntervention;

class BlogImageController extends Controller
{
    public function index(Request $request)
    {
        $images = BlogImage::all();
        return response()->json($images);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $validated = $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'blog_id' => 'required|integer|exists:blogs,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($request->hasFile('image')) {
            try {
                $image_file = $request->file('image');
                $image_file_name = time() . '_' . $image_file->getClientOriginalName();

                // Define the paths
                $file_path = public_path('images/blogs/');
                $file_thumb_path = public_path('images/blogs/thumb/');

                // Ensure directories exist
                File::ensureDirectoryExists($file_path, 0755, true);
                File::ensureDirectoryExists($file_thumb_path, 0755, true);

                // Store original image
                $image_file->storeAs('images/blogs', $image_file_name, 'real_public');

                // Resize and save thumbnail
                $image = ImageIntervention::read($image_file);
                if ($image->width() > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/blogs/thumb', $image_file_name, 'real_public');
                }

                $validated['image'] = $image_file_name;
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Image processing failed.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

        $createdItem = BlogImage::create($validated);

        return response()->json($createdItem, 201);
    }

    public function show($id)
    {
        $blog = BlogImage::find($id);

        if (!$blog) {
            return response()->json(['message' => 'Resource not found.'], 404);
        }

        return response()->json($blog);
    }

     public function update(Request $request, $id)
    {
        // return $request->all();
        $blog_category = BlogImage::find($id);

        if (empty($blog_category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'blog_id' => 'nullable|integer|exists:blogs,id',
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
            $file_path = public_path('images/blogs/');
            $file_thumb_path = public_path('images/blogs/thumb/');

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
            $image_file->storeAs('images/blogs', $image_file_name, 'real_public');

            // Resize and save the image
            try {
                $image = ImageIntervention::read($image_file);
                $original_width = $image->width();
                if ($original_width > 650) {
                    $image->scale(width: 600)->save($file_thumb_path . $image_file_name);
                } else {
                    $image_file->storeAs('images/blogs/thumb', $image_file_name, 'real_public');
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

    public function destroy($id)
    {
        $category = BlogImage::find($id);

        if (empty($category)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        $category->delete();
        return response()->json([
            'message' => 'Delete Success',
        ], 200);
    }
}
