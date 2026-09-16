<?php

namespace Tests\Unit;

use App\Support\PublicUrl;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Every case here uses a literal address, so the tests never need DNS.
 */
class PublicUrlTest extends TestCase
{
    #[DataProvider('blockedUrls')]
    public function test_it_rejects_urls_that_must_not_be_fetched(string $url, string $expectedReason): void
    {
        $reason = PublicUrl::rejectionReason($url);

        $this->assertNotNull($reason, "Expected [{$url}] to be rejected.");
        $this->assertStringContainsString($expectedReason, $reason);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function blockedUrls(): array
    {
        return [
            'loopback' => ['http://127.0.0.1/feed', 'private or reserved'],
            'cloud metadata' => ['http://169.254.169.254/latest/meta-data/', 'private or reserved'],
            'private 10/8' => ['http://10.0.0.5/feed', 'private or reserved'],
            'private 172.16/12' => ['http://172.16.5.4/feed', 'private or reserved'],
            'private 192.168/16' => ['http://192.168.1.1/feed', 'private or reserved'],
            'ipv6 loopback' => ['http://[::1]/feed', 'private or reserved'],
            // No host, so it is caught as a malformed URL before the scheme check.
            'file scheme' => ['file:///etc/passwd', 'not a valid URL'],
            'file scheme with host' => ['file://localhost/etc/passwd', 'Only http and https'],
            'ftp scheme' => ['ftp://198.51.100.7/feed', 'Only http and https'],
            'plain text' => ['not a url', 'not a valid URL'],
            'credentials' => ['http://user:pass@93.184.216.34/feed', 'credentials'],
            'empty' => ['', 'required'],
        ];
    }

    public function test_it_allows_a_public_address(): void
    {
        $this->assertTrue(PublicUrl::isAllowed('http://93.184.216.34/feed'));
        $this->assertTrue(PublicUrl::isWellFormed('https://example.com/feed.xml'));
    }

    public function test_it_classifies_addresses(): void
    {
        $this->assertTrue(PublicUrl::isPublicAddress('8.8.8.8'));
        $this->assertTrue(PublicUrl::isPublicAddress('2606:4700:4700::1111'));

        $this->assertFalse(PublicUrl::isPublicAddress('127.0.0.1'));
        $this->assertFalse(PublicUrl::isPublicAddress('169.254.169.254'));
        $this->assertFalse(PublicUrl::isPublicAddress('::1'));
        $this->assertFalse(PublicUrl::isPublicAddress('0.0.0.0'));
    }

    public function test_the_cheap_check_does_not_need_the_host_to_exist(): void
    {
        // isWellFormed() must stay usable while importing a large OPML file.
        $this->assertTrue(PublicUrl::isWellFormed('https://this-host-does-not-exist.invalid/feed'));
        $this->assertFalse(PublicUrl::isWellFormed('file:///etc/passwd'));
    }
}
