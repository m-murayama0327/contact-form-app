<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
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

    public function test_invalid_tel_fails(): void
    {
        $invalidTels = [
            '090-1234-5678',
            '123456789',
            '123456789012',
        ];

        foreach ($invalidTels as $invalidTel) {
            $input = $this->validContactInput(['tel' => $invalidTel]);

            $validator = Validator::make($input, (new StoreContactRequest)->rules());

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey('tel', $validator->errors()->toArray());
        }
    }
}
