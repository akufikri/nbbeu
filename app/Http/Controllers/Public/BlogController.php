<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = Post::where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return view('public.blog-index', ['posts' => $posts]);
    }

    public function show(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        return view('public.blog-show', ['post' => $post]);
    }
}
