<?php

namespace App\Services;

use App\Models\User;
use App\Support\PublicUrl;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * OPML (Outline Processor Markup Language) is the interchange format every
 * other feed reader understands, which is what makes moving in and out of
 * FeedRead possible.
 */
class OpmlService
{
    /**
     * Build an OPML document for the feeds of a user.
     */
    public function export(User $user): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $opml = $dom->createElement('opml');
        $opml->setAttribute('version', '2.0');
        $dom->appendChild($opml);

        $head = $dom->createElement('head');
        $head->appendChild($dom->createElement('title', 'FeedRead subscriptions'));
        $opml->appendChild($head);

        $body = $dom->createElement('body');
        $opml->appendChild($body);

        foreach ($user->feeds()->orderBy('title')->get() as $feed) {
            // createElement/setAttribute escape the values for us, so a feed
            // title can never break out of the document.
            $outline = $dom->createElement('outline');
            $outline->setAttribute('text', $feed->title);
            $outline->setAttribute('title', $feed->title);
            $outline->setAttribute('type', 'rss');
            $outline->setAttribute('xmlUrl', $feed->url);

            $body->appendChild($outline);
        }

        return (string) $dom->saveXML();
    }

    /**
     * Import subscriptions of a user from an OPML document.
     *
     * @return array{imported: int, skipped: int, invalid: int}
     *
     * @throws InvalidArgumentException When the file cannot be parsed.
     */
    public function import(User $user, string $xml): array
    {
        $result = ['imported' => 0, 'skipped' => 0, 'invalid' => 0];

        $dom = new DOMDocument;

        // LIBXML_NONET keeps the parser from following external references.
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            throw new InvalidArgumentException('The uploaded file is not valid XML.');
        }

        /** @var array<string, true> $known */
        $known = [];

        foreach ($user->feeds()->pluck('url') as $url) {
            $known[Str::lower(trim((string) $url))] = true;
        }

        foreach ((new DOMXPath($dom))->query('//outline[@xmlUrl]') ?: [] as $outline) {
            /** @var \DOMElement $outline */
            $url = trim($outline->getAttribute('xmlUrl'));
            $title = trim($outline->getAttribute('title') ?: $outline->getAttribute('text'));

            // Only a syntax check here: resolving every host of a large OPML
            // file would be slow, and the URL is verified again right before
            // the feed is fetched.
            if (! PublicUrl::isWellFormed($url)) {
                $result['invalid']++;

                continue;
            }

            $key = Str::lower($url);

            if (isset($known[$key])) {
                $result['skipped']++;

                continue;
            }

            $user->feeds()->create([
                'title' => Str::limit($title !== '' ? $title : (string) parse_url($url, PHP_URL_HOST), 255, ''),
                'url' => $url,
            ]);

            $known[$key] = true;
            $result['imported']++;
        }

        return $result;
    }
}
