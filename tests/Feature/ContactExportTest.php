<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\LogsInAsAdmin;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use LogsInAsAdmin;
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/contacts/export')->assertRedirect('/login');
    }

    public function test_exports_csv_with_filters(): void
    {
        $this->loginAsAdmin();
        $maleContact = Contact::factory()->create(['gender' => 1, 'email' => 'male@example.com']);
        $femaleContact = Contact::factory()->create(['gender' => 2, 'email' => 'female@example.com']);

        $response = $this->get('/contacts/export?gender=1');

        $response->assertOk();
        $response->assertDownload('contacts.csv');
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF".'ID,氏名,性別,メール,電話,住所,建物,カテゴリ,内容,作成日時', $csv);
        $this->assertStringContainsString($maleContact->email, $csv);
        $this->assertStringContainsString('男性', $csv);
        $this->assertStringNotContainsString($femaleContact->email, $csv);
    }

    public function test_exports_all_in_newest_order_without_filters(): void
    {
        $this->loginAsAdmin();
        $oldContact = Contact::factory()->create(['email' => 'old@example.com', 'created_at' => now()->subDay()]);
        $newContact = Contact::factory()->create(['email' => 'new@example.com']);

        $response = $this->get('/contacts/export');

        $csv = $response->streamedContent();
        $this->assertStringContainsString($oldContact->email, $csv);
        $this->assertStringContainsString($newContact->email, $csv);
        $this->assertLessThan(strpos($csv, $oldContact->email), strpos($csv, $newContact->email));
    }
}
