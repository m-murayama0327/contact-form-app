<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Tag;

trait CreatesContactInput
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validContactInput(array $overrides = []): array
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        return array_merge([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区千駄ヶ谷1-2-3',
            'building' => '千駄ヶ谷マンション305',
            'category_id' => $category->id,
            'detail' => 'お問い合わせの内容です。',
            'tag_ids' => $tags->pluck('id')->all(),
        ], $overrides);
    }
}
