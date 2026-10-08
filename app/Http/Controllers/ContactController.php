<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    public function confirm(StoreContactRequest $request): View
    {
        $validated = $request->validated();
        $category = Category::find($validated['category_id']);
        $tags = Tag::whereIn('id', $validated['tag_ids'] ?? [])->get();

        // 「修正」で入力ページに戻ったときに、入力値を old() で表示できるようにする
        $request->flash();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $contact = Contact::create(Arr::except($validated, 'tag_ids'));
            $contact->tags()->attach($validated['tag_ids'] ?? []);
        });

        return redirect('/thanks');
    }

    public function thanks(): View
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $request): StreamedResponse
    {
        $contacts = Contact::with('category')
            ->search($request->validated())
            ->latest()
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($contacts) {
            $stream = fopen('php://output', 'w');

            // Excel で開いたときに文字化けしないように、先頭に BOM を付ける
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'], escape: '');

            foreach ($contacts as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    "{$contact->first_name} {$contact->last_name}",
                    Contact::GENDER_LABELS[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content,
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ], escape: '');
            }

            fclose($stream);
        }, 'contacts.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
