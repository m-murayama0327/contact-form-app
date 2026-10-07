<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_many_contacts(): void
    {
        $category = Category::factory()->create();
        $contacts = Contact::factory()->count(3)->create(['category_id' => $category->id]);
        // ほかのカテゴリのお問い合わせが混ざらないことを確かめるために作る
        Contact::factory()->create();

        $categoryContacts = $category->contacts;

        $this->assertCount(3, $categoryContacts);
        $this->assertEqualsCanonicalizing(
            $contacts->pluck('id')->all(),
            $categoryContacts->pluck('id')->all()
        );
    }
}
