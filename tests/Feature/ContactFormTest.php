<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesContactInput;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use CreatesContactInput;
    use RefreshDatabase;

    public function test_index_page_displays_categories_and_tags(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('contact.index');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    public function test_thanks_page_is_displayed(): void
    {
        $response = $this->get('/thanks');

        $response->assertOk();
        $response->assertViewIs('contact.thanks');
    }

    public function test_confirm_page_displays_input(): void
    {
        $input = $this->validContactInput();
        $category = Category::find($input['category_id']);
        $tags = Tag::whereIn('id', $input['tag_ids'])->get();

        $response = $this->post('/contacts/confirm', $input);

        $response->assertOk();
        $response->assertViewIs('contact.confirm');
        $response->assertSee($input['first_name']);
        $response->assertSee($input['last_name']);
        $response->assertSee($input['email']);
        $response->assertSee($category->content);
        foreach ($tags as $tag) {
            $response->assertSee($tag->name);
        }
    }

    public function test_confirm_validation_error_redirects(): void
    {
        $input = $this->validContactInput(['first_name' => '']);

        $response = $this->from('/')->post('/contacts/confirm', $input);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('first_name');
    }

    public function test_store_saves_contact_and_tags(): void
    {
        $input = $this->validContactInput();

        $response = $this->post('/contacts', $input);

        $response->assertRedirect('/thanks');
        $this->assertDatabaseHas('contacts', [
            'category_id' => $input['category_id'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'tel' => $input['tel'],
        ]);
        $contact = Contact::where('email', $input['email'])->first();
        foreach ($input['tag_ids'] as $tagId) {
            $this->assertDatabaseHas('contact_tag', [
                'contact_id' => $contact->id,
                'tag_id' => $tagId,
            ]);
        }
    }

    public function test_store_validation_error_redirects(): void
    {
        $input = $this->validContactInput(['first_name' => '']);

        $response = $this->from('/')->post('/contacts', $input);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('first_name');
        $this->assertDatabaseCount('contacts', 0);
    }
}
