<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $contact = Contact::factory()->create();

        $this->get('/admin')->assertRedirect('/login');
        $this->get("/admin/contacts/{$contact->id}")->assertRedirect('/login');
        $this->delete("/admin/contacts/{$contact->id}")->assertRedirect('/login');
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }

    public function test_authenticated_user_can_view_admin(): void
    {
        $this->loginAsAdmin();

        $response = $this->get('/admin');

        $response->assertOk();
        $response->assertViewIs('admin.index');
    }

    public function test_search_by_keyword(): void
    {
        $this->loginAsAdmin();
        $sato = Contact::factory()->create(['first_name' => '佐藤', 'email' => 'sato@example.com']);
        $suzuki = Contact::factory()->create(['first_name' => '鈴木', 'email' => 'suzuki@example.com']);

        $nameResponse = $this->get('/admin?keyword=佐藤');
        $emailResponse = $this->get('/admin?keyword=suzuki@example');

        $nameResponse->assertSee($sato->email);
        $nameResponse->assertDontSee($suzuki->email);
        $emailResponse->assertSee($suzuki->email);
        $emailResponse->assertDontSee($sato->email);
    }

    public function test_search_by_gender(): void
    {
        $this->loginAsAdmin();
        $maleContact = Contact::factory()->create(['gender' => 1]);
        $femaleContact = Contact::factory()->create(['gender' => 2]);

        $response = $this->get('/admin?gender=1');

        $response->assertSee($maleContact->email);
        $response->assertDontSee($femaleContact->email);
    }

    public function test_search_by_category(): void
    {
        $this->loginAsAdmin();
        $category = Category::factory()->create();
        $categoryContact = Contact::factory()->create(['category_id' => $category->id]);
        $otherContact = Contact::factory()->create();

        $response = $this->get("/admin?category_id={$category->id}");

        $response->assertSee($categoryContact->email);
        $response->assertDontSee($otherContact->email);
    }

    public function test_search_by_date(): void
    {
        $this->loginAsAdmin();
        $targetContact = Contact::factory()->create(['created_at' => '2026-10-01 10:00:00']);
        $otherContact = Contact::factory()->create(['created_at' => '2026-10-02 10:00:00']);

        $response = $this->get('/admin?date=2026-10-01');

        $response->assertSee($targetContact->email);
        $response->assertDontSee($otherContact->email);
    }

    public function test_paginates_by_seven(): void
    {
        $this->loginAsAdmin();
        Contact::factory()->count(8)->create();

        $firstPageResponse = $this->get('/admin');
        $secondPageResponse = $this->get('/admin?page=2');

        $this->assertCount(7, $firstPageResponse->viewData('contacts'));
        $this->assertCount(1, $secondPageResponse->viewData('contacts'));
    }

    public function test_show_displays_contact_with_category(): void
    {
        $this->loginAsAdmin();
        $category = Category::factory()->create();
        $contact = Contact::factory()->create(['category_id' => $category->id]);

        $response = $this->get("/admin/contacts/{$contact->id}");

        $response->assertOk();
        $response->assertViewIs('admin.show');
        $response->assertSee($contact->email);
        $response->assertSee($category->content);
    }

    public function test_destroy_deletes_contact(): void
    {
        $this->loginAsAdmin();
        $contact = Contact::factory()->create();
        $tag = Tag::factory()->create();
        $contact->tags()->attach($tag->id);

        $response = $this->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
        $this->assertDatabaseMissing('contact_tag', ['contact_id' => $contact->id]);
    }

    private function loginAsAdmin(): void
    {
        $this->actingAs(User::factory()->create());
    }
}
