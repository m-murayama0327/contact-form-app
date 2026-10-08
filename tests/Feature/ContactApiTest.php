<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesContactInput;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use CreatesContactInput;
    use RefreshDatabase;

    private const NOT_FOUND_JSON = ['error' => 'お問い合わせが見つかりませんでした。'];

    public function test_index_returns_list(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts?per_page=2');

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJsonStructure([
            'data' => ['*' => ['id', 'category' => ['id', 'content'], 'first_name', 'tags', 'created_at']],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonPath('meta.per_page', 2);
        $response->assertJsonPath('meta.total', 3);
    }

    public function test_index_filters_results(): void
    {
        $maleContact = Contact::factory()->create(['gender' => 1]);
        Contact::factory()->create(['gender' => 2]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $maleContact->id);
    }

    public function test_index_validation_error_returns422(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=0');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender' => '性別の値が不正です']);
    }

    public function test_show_returns_contact(): void
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertJsonPath('data.id', $contact->id);
        $response->assertJsonPath('data.category.id', $contact->category_id);
        $response->assertJsonPath('data.tags.0.id', $tag->id);
    }

    public function test_show_returns404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/contacts/9999');

        $response->assertNotFound();
        $response->assertExactJson(self::NOT_FOUND_JSON);
    }

    public function test_store_creates_contact(): void
    {
        $input = $this->validContactInput();

        $response = $this->postJson('/api/v1/contacts', $input);

        $response->assertCreated();
        $response->assertJsonPath('data.email', $input['email']);
        $response->assertJsonCount(2, 'data.tags');
        $this->assertDatabaseHas('contacts', ['email' => $input['email'], 'category_id' => $input['category_id']]);
        $this->assertDatabaseCount('contact_tag', 2);
    }

    public function test_store_validation_error_returns422(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name' => '姓を入力してください']);
        $this->assertDatabaseCount('contacts', 0);
    }

    public function test_update_updates_contact(): void
    {
        $contact = Contact::factory()->create();
        $oldTag = Tag::factory()->create();
        $contact->tags()->attach($oldTag->id);
        $input = $this->validContactInput(['first_name' => '更新後の姓']);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $input);

        $response->assertOk();
        $response->assertJsonPath('data.first_name', '更新後の姓');
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'first_name' => '更新後の姓']);
        $this->assertDatabaseMissing('contact_tag', ['contact_id' => $contact->id, 'tag_id' => $oldTag->id]);
        $this->assertDatabaseCount('contact_tag', 2);
    }

    public function test_update_returns404_when_not_found(): void
    {
        $response = $this->putJson('/api/v1/contacts/9999', $this->validContactInput());

        $response->assertNotFound();
        $response->assertExactJson(self::NOT_FOUND_JSON);
    }

    public function test_update_validation_error_returns422(): void
    {
        $contact = Contact::factory()->create(['first_name' => '更新前の姓']);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name' => '姓を入力してください']);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id, 'first_name' => '更新前の姓']);
    }

    public function test_destroy_deletes_contact(): void
    {
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('contact_tag', ['contact_id' => $contact->id]);
    }

    public function test_destroy_returns404_when_not_found(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/9999');

        $response->assertNotFound();
        $response->assertExactJson(self::NOT_FOUND_JSON);
    }
}
