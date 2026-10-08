<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\LogsInAsAdmin;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use LogsInAsAdmin;
    use RefreshDatabase;

    public function test_guest_cannot_manage_tags(): void
    {
        $tag = Tag::factory()->create(['name' => '変更前のタグ']);

        $this->get("/admin/tags/{$tag->id}/edit")->assertRedirect('/login');
        $this->post('/admin/tags', ['name' => '追加するタグ'])->assertRedirect('/login');
        $this->put("/admin/tags/{$tag->id}", ['name' => '変更後のタグ'])->assertRedirect('/login');
        $this->delete("/admin/tags/{$tag->id}")->assertRedirect('/login');
        $this->assertDatabaseMissing('tags', ['name' => '追加するタグ']);
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '変更前のタグ']);
    }

    public function test_edit_page_is_displayed(): void
    {
        $this->loginAsAdmin();
        $tag = Tag::factory()->create();

        $response = $this->get("/admin/tags/{$tag->id}/edit");

        $response->assertOk();
        $response->assertViewIs('admin.tags.edit');
        $response->assertSee($tag->name);
    }

    public function test_store_creates_tag(): void
    {
        $this->loginAsAdmin();

        $response = $this->post('/admin/tags', ['name' => '追加するタグ']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['name' => '追加するタグ']);
    }

    public function test_update_updates_tag(): void
    {
        $this->loginAsAdmin();
        $tag = Tag::factory()->create(['name' => '変更前のタグ']);

        $response = $this->put("/admin/tags/{$tag->id}", ['name' => '変更後のタグ']);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => '変更後のタグ']);
    }

    public function test_destroy_deletes_tag(): void
    {
        $this->loginAsAdmin();
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
        $this->assertDatabaseMissing('contact_tag', ['tag_id' => $tag->id]);
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }
}
