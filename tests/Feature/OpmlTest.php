<?php

namespace Tests\Feature;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class OpmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_exports_the_feeds_of_the_signed_in_user(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        Feed::factory()->create([
            'user_id' => $me->id,
            'title' => 'News',
            'url' => 'https://example.com/feed.xml',
        ]);
        Feed::factory()->create([
            'user_id' => $other->id,
            'title' => 'Secret',
            'url' => 'https://secret.example.com/feed.xml',
        ]);

        $response = $this->actingAs($me)->get(route('opml.export'));

        $response->assertOk()->assertHeader('content-disposition');

        $body = $response->getContent();

        $this->assertStringContainsString('xmlUrl="https://example.com/feed.xml"', $body);
        // OPML keeps the feed title in the outline attributes.
        $this->assertStringContainsString('text="News"', $body);
        $this->assertStringContainsString('type="rss"', $body);
        $this->assertStringNotContainsString('secret.example.com', $body);
    }

    public function test_a_feed_title_cannot_break_out_of_the_document(): void
    {
        $user = User::factory()->create();

        Feed::factory()->create([
            'user_id' => $user->id,
            'title' => '"><script>alert(1)</script>',
            'url' => 'https://example.com/feed.xml',
        ]);

        $body = $this->actingAs($user)->get(route('opml.export'))->getContent();

        $this->assertStringNotContainsString('<script>', $body);
        $this->assertStringContainsString('&lt;script&gt;', $body);
    }

    public function test_it_imports_feeds_from_an_uploaded_file(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('opml.import'), ['file' => $this->opmlFile()])
            ->assertRedirect(route('opml.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'News',
            'url' => 'https://example.com/feed.xml',
        ]);
        $this->assertDatabaseHas('feeds', [
            'user_id' => $user->id,
            'title' => 'Blog',
            'url' => 'https://blog.example.org/rss',
        ]);
    }

    public function test_it_skips_feeds_the_account_already_has(): void
    {
        $user = User::factory()->create();

        Feed::factory()->create([
            'user_id' => $user->id,
            'url' => 'https://example.com/feed.xml',
        ]);

        $this->actingAs($user)->post(route('opml.import'), ['file' => $this->opmlFile()]);

        $this->assertDatabaseCount('feeds', 2);
    }

    public function test_it_rejects_entries_that_are_not_public_http_urls(): void
    {
        $user = User::factory()->create();

        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <head><title>Bad</title></head>
          <body>
            <outline text="Local" xmlUrl="http://127.0.0.1/feed"/>
            <outline text="Metadata" xmlUrl="http://169.254.169.254/feed"/>
            <outline text="Disk" xmlUrl="file:///etc/passwd"/>
            <outline text="Fine" xmlUrl="https://example.com/ok.xml"/>
          </body>
        </opml>
        XML;

        $this->actingAs($user)->post(route('opml.import'), [
            'file' => UploadedFile::fake()->createWithContent('subscriptions.opml', $xml),
        ]);

        $this->assertDatabaseCount('feeds', 1);
        $this->assertDatabaseHas('feeds', ['url' => 'https://example.com/ok.xml']);
    }

    public function test_a_file_that_is_not_xml_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('opml.import'), [
                'file' => UploadedFile::fake()->createWithContent('subscriptions.opml', 'this is not xml'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('feeds', 0);
    }

    public function test_an_opml_file_is_required(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('opml.import'), [])
            ->assertSessionHasErrors('file');
    }

    private function opmlFile(): UploadedFile
    {
        $xml = <<<'XML'
        <?xml version="1.0" encoding="UTF-8"?>
        <opml version="2.0">
          <head><title>subscriptions</title></head>
          <body>
            <outline text="Tech">
              <outline text="News" type="rss" xmlUrl="https://example.com/feed.xml"/>
            </outline>
            <outline text="Blog" type="rss" xmlUrl="https://blog.example.org/rss"/>
          </body>
        </opml>
        XML;

        return UploadedFile::fake()->createWithContent('subscriptions.opml', $xml);
    }
}
