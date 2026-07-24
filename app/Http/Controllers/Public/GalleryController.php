<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $items = GalleryItem::where('is_active', true)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('display_order')
            ->paginate(12)
            ->withQueryString();

        $categories = GalleryItem::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('public.gallery', [
            'items' => $items,
            'categories' => $categories,
            'category' => $category,
        ]);
    }
}
