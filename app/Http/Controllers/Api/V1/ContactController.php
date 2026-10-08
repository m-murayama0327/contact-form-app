<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = DB::transaction(function () use ($validated) {
            $contact = Contact::create(Arr::except($validated, 'tag_ids'));
            $contact->tags()->attach($validated['tag_ids'] ?? []);

            return $contact;
        });
        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $contact) {
            $contact->update(Arr::except($validated, 'tag_ids'));
            $contact->tags()->sync($validated['tag_ids'] ?? []);
        });
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
