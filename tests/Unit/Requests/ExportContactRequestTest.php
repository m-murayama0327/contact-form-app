<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_filters_pass(): void
    {
        $category = Category::factory()->create();
        $filters = [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-10-01',
        ];

        $validator = Validator::make($filters, (new ExportContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_fails(): void
    {
        $invalidGenders = [4, -1, 'male'];

        foreach ($invalidGenders as $invalidGender) {
            $filters = ['gender' => $invalidGender];

            $validator = Validator::make($filters, (new ExportContactRequest)->rules());

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        }
    }

    public function test_nonexistent_category_fails(): void
    {
        $category = Category::factory()->create();
        $filters = ['category_id' => $category->id + 1];

        $validator = Validator::make($filters, (new ExportContactRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }
}
