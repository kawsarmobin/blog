<?php

namespace App\Http\Controllers;

use App\Tag;
use App\Post;
use App\Setting;
use App\Category;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function index()
    {
        return view('index')
                ->with('title', Setting::first()->site_name)
                ->with('categories', Category::take(5)->get())
                ->with('first_post', Post::orderBy('created_at', 'desc')->first())
                ->with('second_post', Post::orderBy('created_at', 'desc')->skip(1)->take(1)->get()->first())
                ->with('third_post', Post::orderBy('created_at', 'desc')->skip(2)->take(1)->get()->first())
                ->with('laravel', Category::find(7))
                ->with('ruby', Category::find(10))
                ->with('settings', Setting::first());
    }


    public function singlePost($slug)
    {
        $post = Post::where('slug', $slug)->first();

        $next_id = Post::where('id', '>', $post->id)->min('id');
        $prev_id = Post::where('id', '<', $post->id)->max('id');

        return view('single')
                ->with('title', $post->title)
                ->with('settings', Setting::first())
                ->with('categories', Category::take(5)->get())
                ->with('post', $post)
                ->with('next', Post::find($next_id))
                ->with('prev', Post::find($prev_id))
                ->with('tags', Tag::all());
    }


    public function category($id)
    {
        $category = Category::find($id);

        return view('category')
                ->with('category', $category)
                ->with('title', $category->name)
                ->with('settings', Setting::first())
                ->with('categories', Category::take(5)->get())
                ->with('tags', Tag::all());
    }


    public function tag($id)
    {
        $tag = Tag::find($id);

        return view('tag')
                ->with('tag', $tag)
                ->with('title', $tag->tag)
                ->with('settings', Setting::first())
                ->with('categories', Category::take(5)->get())
                ->with('tags', Tag::all());
    }
}
