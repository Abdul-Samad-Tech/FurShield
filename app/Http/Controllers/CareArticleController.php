<?php

namespace App\Http\Controllers;

use App\Enums\CareCategory;
use App\Models\CareArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = CareArticle::query()
            ->where('is_published', true)
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)->orWhere('body', 'like', $term);
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $categories = CareCategory::cases();

        return view('care.index', compact('articles', 'categories'));
    }

    public function show(CareArticle $article): View
    {
        abort_unless($article->is_published, 404);

        return view('care.show', compact('article'));
    }
}
