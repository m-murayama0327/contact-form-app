<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $contacts = Contact::with(['category', 'tags'])
            ->search($filters)
            ->latest()
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20);

        return ContactResource::collection($contacts);
    }

    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }
}
