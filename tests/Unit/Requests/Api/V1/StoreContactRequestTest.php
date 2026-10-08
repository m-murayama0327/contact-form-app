<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\Concerns\CreatesContactInput;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use CreatesContactInput;
    use RefreshDatabase;

    public function test_valid_data_passes(): void
    {
        $input = $this->validContactInput();

        $validator = Validator::make($input, (new StoreContactRequest)->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_data_fails(): void
    {
        $deletedTag = Tag::factory()->create();
        $deletedTag->delete();
        $invalidValues = [
            'tel' => '090-1234-5678',
            'gender' => 0,
            'tag_ids' => [$deletedTag->id],
        ];

        foreach ($invalidValues as $key => $invalidValue) {
            $input = $this->validContactInput([$key => $invalidValue]);

            $validator = Validator::make($input, (new StoreContactRequest)->rules());

            $this->assertTrue($validator->fails());
            $this->assertNotEmpty($validator->errors()->get("{$key}*"));
        }
    }
}
