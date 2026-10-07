<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_data_passes(): void
    {
        $input = $this->validInput();

        $validator = Validator::make($input, (new StoreContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_tel_fails(): void
    {
        $invalidTels = [
            '090-1234-5678',
            '123456789',
            '123456789012',
        ];

        foreach ($invalidTels as $invalidTel) {
            $input = $this->validInput(['tel' => $invalidTel]);

            $validator = Validator::make($input, (new StoreContactRequest)->rules());

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey('tel', $validator->errors()->toArray());
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validInput(array $overrides = []): array
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
