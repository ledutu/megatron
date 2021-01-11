<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Post;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['only' => ['addPost', 'hidePost']]);
        // $this->middleware('adminChecking', ['only' => ['addPost', 'hidePost']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        //
        $posts = Post::all();
        return $this->response(200, ['posts' => $posts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addPost(Request $request)
    {
        //

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'nullable',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $post = new Post();
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = 1;
        $post->save();

        return $this->response(200, ['post' => $post], __('Add successful'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function findPost($id)
    {
        //
        $post = Post::find($id);

        return $this->response(200, ['post' => $post], $post ? __('Get post successful') : __('Not found'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function hidePost($id)
    {
        //
        $post = Post::find($id);
        if (!$post) {
            return $this->response(200, [], __('Cannot found this post'));
        }
        $post->status = $post->status ? 0 : 1;
        $post->save();
        return $this->response(200, ['post' => $post], !$post->status ? __('Hide successful') : __('Show successfull'));
    }
}
