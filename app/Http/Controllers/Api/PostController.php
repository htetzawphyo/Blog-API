<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use Exception;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;

class PostController extends Controller
{

    public function index(Request $request) {
        $query = Post::with('user', 'category', 'image')->orderByDesc('created_at');

        if($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if($request->search) {
            $query->where(function ($q1) use ($request) {
               $q1->where('title', 'like', '%'. $request->search .'%')
                    ->orwhere('description', 'like', '%'. $request->search . '%'); 
            });
        }

        $posts = $query->paginate(10);

        return PostResource::collection($posts)->additional(['message' => 'success']);
    }

    public function show($id) {
        $post = Post::with('user', 'category', 'image')->where('id', $id)->firstOrFail();
        return ResponseHelper::success(new PostDetailResource($post));
    }

    public function create(Request $request) {
        $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'category_id' => 'required'
        ],[
            'category_id.required' => 'The category field is required.'
        ]);

        DB::beginTransaction();
        try{
            if($request->hasFile('image')){
                $file = $request->file('image');
                $img_name = time() . '_' . $request->file('image')->getClientOriginalName();
                Storage::put('media/' . $img_name, file_get_contents($file));
            }
    
            $post = new Post();
            $post->user_id = auth()->user()->id;
            $post->title = $request->title;
            $post->description = $request->description;
            $post->category_id = $request->category_id;
            $post->save();
    
            $media = new Media();
            $media->file_name = $img_name;
            $media->file_type = 'image';
            $media->model_id = $post->id;
            $media->model_type = Post::class;
            $media->save();
    
            DB::commit();
            return ResponseHelper::success([], 'Successfully uploaded.');
        } catch(Exception $err) {
            DB::rollBack();
            return ResponseHelper::fail($err->getMessage());
        }

        
    }
}
