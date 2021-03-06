<?php

namespace App\Http\Controllers;

use Auth;
use Session;
use App\Tag;
use App\Post;
use App\Category;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.posts.index')
                ->with('posts', Post::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        if ($categories->count() == 0 || $tags->count() == 0) {
          Session::flash('info', 'You must have some categories and tags before attempting to create a post.');

          return redirect()->back();
        }

        return view('admin.posts.create')
                ->with('categories', $categories)
                ->with('tags', Tag::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $this->validate($request,[
        'category_id' => 'required',
        'title' => 'required|max:255',
        'featured' => 'required|image',
        'content' => 'required',
      ]);

      $featured = $request->featured;

      $featured_new_name = time().'-'.$featured->getClientOriginalName();

      $featured->move('uploads/posts', $featured_new_name);

      $post = Post::create([
        'category_id' => $request->category_id,
        'title' => $request->title,
        'featured' => 'uploads/posts/' . $featured_new_name,
        'content' => $request->content,
        'slug' => str_slug($request->title),
        'user_id' => Auth::id(),
      ]);

      $post->tags()->attach($request->tags);

      Session::flash('success', 'Post create successfully.');

      return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      return view('admin.posts.edit')
              ->with('categories', Category::all())
              ->with('post', Post::find($id))
              ->with('tags', Tag::all());
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
      $this->validate($request,[
        'category_id' => 'required',
        'title' => 'required|max:255',
        'featured' => 'image',
        'content' => 'required',
      ]);

      $post = Post::find($id);

      if ($request->hasFile('featured')) {

        $featured = $request->featured;
        $featured_new_name = time().'-'.$featured->getClientOriginalName();
        $featured->move('uploads/posts', $featured_new_name);
        $post->featured = 'uploads/posts/' . $featured_new_name;
      }

      $post->category_id = $request->category_id;
      $post->title = $request->title;
      $post->content = $request->content;

      $post->tags()->sync($request->tags);

      if ($post->save()) {
        Session::flash('success', 'Post update successfully.');
      }

      return redirect()->route('posts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if ($post->delete()) {
          Session::flash('success', 'Your post was just trashed.');
        }

        return redirect()->back();
    }

    public function trashed()
    {
      $posts = Post::onlyTrashed()->get();

      return view('admin.posts.trashed')
              ->with('posts', $posts);
    }

    public function permanentlyDeleted($id)
    {
      $post = Post::withTrashed()->where('id', $id)->first();

      if ($post->forceDelete()) {
        // unlink('uploads/posts/' . $post->getOriginal('featured'));
        Session::flash('success', 'Post permanently deleted.');
      }

      return redirect()->back();
    }

    public function restore($id)
    {
      $post = Post::withTrashed()->where('id', $id)->first();

      $post->restore();

      Session::flash('success', 'Post permanently deleted.');

      return redirect()->route('posts');
    }
}
