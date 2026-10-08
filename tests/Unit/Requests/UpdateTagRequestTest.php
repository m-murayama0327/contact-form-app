<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_name_as_self_passes(): void
    {
        $editingTag = Tag::factory()->create();
        $input = ['name' => $editingTag->name];

        $validator = Validator::make($input, $this->getRulesForEditing($editingTag));

        $this->assertTrue($validator->passes());
    }

    public function test_name_used_by_other_tag_fails(): void
    {
        $editingTag = Tag::factory()->create();
        $otherTag = Tag::factory()->create();
        $input = ['name' => $otherTag->name];

        $validator = Validator::make($input, $this->getRulesForEditing($editingTag));

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function getRulesForEditing(Tag $tag): array
    {
        // UpdateTagRequest は URL の {tag} から編集中のタグを取り出すので、そのタグが入った Route を持たせる
        $request = UpdateTagRequest::create("/admin/tags/{$tag->id}", 'PUT');
        $request->setRouteResolver(function () use ($request, $tag) {
            $route = (new Route('PUT', '/admin/tags/{tag}', fn () => null))->bind($request);
            $route->setParameter('tag', $tag);

            return $route;
        });

        return $request->rules();
    }
}
