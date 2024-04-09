<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::all();
        return $posts;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $hashedString = User::where('email', $request)->get();
        return $hashedString;
        // In your store method
        // $post = Post::create([
        //     'title' => $request->input('title'),
        //     'body' => $request->input('body'),
        // ]);
        // $post->save();
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Post::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->update($request->all());

        return $post;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $post = Post::find($id);

        if ($post) {
            $post->delete();
            return 'post' . $id . 'deleted';
        } else {
            return 'post not found';
        }
    }
}
