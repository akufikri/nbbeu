<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $category = $request->query('category');

        $posts = Post::where('status', 'published')
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($category && array_key_exists($category, Post::CATEGORIES), fn ($query) => $query->where('category', $category))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $data = [
            'posts' => $posts,
            'search' => $search,
            'category' => $category,
        ];

        if ($request->ajax()) {
            return view('public.blog._results', $data);
        }

        return view('public.blog.index', $data);
    }

    public function show(Post $post): View
    {
        abort_unless($post->status === 'published', 404);

        return view('public.blog.show', ['post' => $post]);
    }
}
