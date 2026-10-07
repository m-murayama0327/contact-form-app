<?php

namespace Tests\Unit\Models;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_many_contacts(): void
    {
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(2)->create();
        foreach ($contacts as $contact) {
            $contact->tags()->attach($tag->id);
        }

        $tagContacts = $tag->contacts;

        $this->assertCount(2, $tagContacts);
        $this->assertEqualsCanonicalizing(
            $contacts->pluck('id')->all(),
            $tagContacts->pluck('id')->all()
        );
    }
}
