<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_seeds_initial_data(): void
    {
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('categories', 5);
        $this->assertDatabaseCount('tags', 5);
        $this->assertDatabaseCount('contacts', 20);
    }

    public function test_admin_can_login(): void
    {
        $isAuthenticated = Auth::attempt([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertTrue($isAuthenticated);
    }

    public function test_each_contact_has_one_to_three_tags(): void
    {
        $contacts = Contact::withCount('tags')->get();

        foreach ($contacts as $contact) {
            $this->assertGreaterThanOrEqual(1, $contact->tags_count);
            $this->assertLessThanOrEqual(3, $contact->tags_count);
        }
    }
}
