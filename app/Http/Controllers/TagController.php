<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;

class TagController extends Controller
{
    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect('/admin');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect('/admin');
    }
}
