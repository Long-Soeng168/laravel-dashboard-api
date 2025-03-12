<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;



class BlogController extends Controller
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

        $query = Blog::query();

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
                'short_description' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'blog_category_code' => 'nullable|string',
                'order_index' => 'nullable|string|max:255',
                'created_by' => 'nullable|string|max:255',
                'updated_by' => 'nullable|string|max:255',


            ]);
            // return $validated;
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }


        $createdItem = Blog::create($validated);
        return response()->json($createdItem, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $blog = Blog::find($id);

        if (empty($blog)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        return response()->json($blog);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // return $request->all();
        $blog = Blog::find($id);

        if (empty($blog)) {
            return response()->json([
                'message' => 'Resource not found.',
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'title_kh' => 'required|string|max:255',
                'short_description' => 'nullable|string|max:255',
                'long_description' => 'nullable|string',
                'blog_category_code' => 'nullable|string',
                'order_index' => 'nullable|string|max:255',
                'created_by' => 'nullable|string|max:255',
                'updated_by' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }


        $blog->update($validated);

        return response()->json([
            'message' => 'Category updated successfully.',
            'category' => $blog,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Blog::find($id);

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
