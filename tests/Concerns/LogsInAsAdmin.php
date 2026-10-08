<?php

namespace Tests\Concerns;

use App\Models\User;

trait LogsInAsAdmin
{
    protected function loginAsAdmin(): void
    {
        $this->actingAs(User::factory()->create());
    }
}
