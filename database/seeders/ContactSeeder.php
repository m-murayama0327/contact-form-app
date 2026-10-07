<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $tags = Tag::all();

        Contact::factory()
            ->count(20)
            ->recycle($categories)
            ->create()
            ->each(function (Contact $contact) use ($tags) {
                $tagIds = $tags->random(fake()->numberBetween(1, 3))->pluck('id');

                $contact->tags()->attach($tagIds);
            });
    }
}
