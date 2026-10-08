<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
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
            'page' => 2,
            'per_page' => 100,
        ];

        $validator = Validator::make($filters, (new IndexContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_values_fail(): void
    {
        $invalidFilters = [
            'gender' => 0,
            'page' => 0,
            'per_page' => 101,
        ];

        foreach ($invalidFilters as $key => $invalidValue) {
            $filters = [$key => $invalidValue];

            $validator = Validator::make($filters, (new IndexContactRequest)->rules());

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey($key, $validator->errors()->toArray());
        }
    }
}
