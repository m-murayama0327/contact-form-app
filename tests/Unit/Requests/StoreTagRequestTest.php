<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_name_is_required(): void
    {
        $input = ['name' => ''];

        $validator = Validator::make($input, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_name_over_max_length_fails(): void
    {
        $input = ['name' => str_repeat('あ', 51)];

        $validator = Validator::make($input, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_duplicate_name_fails(): void
    {
        $existingTag = Tag::factory()->create();
        $input = ['name' => $existingTag->name];

        $validator = Validator::make($input, (new StoreTagRequest)->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }
}
