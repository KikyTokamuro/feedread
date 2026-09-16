<?php

namespace App\Support;

/**
 * Guards outbound requests against Server Side Request Forgery.
 *
 * Feed URLs and proxied URLs are supplied by whoever uses the app, so they must
 * never be able to point the server at itself, at the private network or at a
 * link-local address such as the cloud metadata endpoint (169.254.169.254).
 */
class PublicUrl
{
    /**
     * URL schemes we are willing to fetch.
     *
     * @var array<int, string>
     */
    public const ALLOWED_SCHEMES = ['http', 'https'];

    /**
     * Check that a URL is syntactically valid, uses an allowed scheme and
     * resolves only to publicly routable addresses. Performs DNS lookups.
     */
    public static function isAllowed(?string $url): bool
    {
        return static::rejectionReason($url) === null;
    }

    /**
     * Explain why a URL may not be fetched, or null when it is allowed.
     *
     * This resolves the host name, so only call it right before an outbound
     * request (or when the reply time does not matter).
     */
    public static function rejectionReason(?string $url): ?string
    {
        if (($reason = static::syntaxRejectionReason($url)) !== null) {
            return $reason;
        }

        // parse_url keeps the square brackets around IPv6 literals.
        $host = trim((string) parse_url(trim((string) $url), PHP_URL_HOST), '[]');

        $addresses = filter_var($host, FILTER_VALIDATE_IP)
            ? [$host]
            : static::resolve($host);

        if ($addresses === []) {
            return 'The host could not be resolved.';
        }

        foreach ($addresses as $address) {
            if (! static::isPublicAddress($address)) {
                return 'The URL points to a private or reserved network address.';
            }
        }

        return null;
    }

    /**
     * Cheap structural check without any DNS lookups. Used when importing a
     * large OPML file, where resolving every host would be too slow; the host
     * is still verified before the feed is actually fetched.
     */
    public static function isWellFormed(?string $url): bool
    {
        return static::syntaxRejectionReason($url) === null;
    }

    /**
     * Check scheme, host and credentials.
     */
    protected static function syntaxRejectionReason(?string $url): ?string
    {
        if (blank($url) || ! is_string($url)) {
            return 'The URL is required.';
        }

        $parts = parse_url(trim($url));

        if ($parts === false || empty($parts['host'])) {
            return 'The URL is not a valid URL.';
        }

        if (! in_array(strtolower($parts['scheme'] ?? ''), self::ALLOWED_SCHEMES, true)) {
            return 'Only http and https URLs are supported.';
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return 'URLs containing credentials are not supported.';
        }

        // A literal address needs no DNS, so an obvious internal target (the
        // cloud metadata IP, localhost) is caught even by the cheap check.
        $host = trim($parts['host'], '[]');

        if (filter_var($host, FILTER_VALIDATE_IP) && ! static::isPublicAddress($host)) {
            return 'The URL points to a private or reserved network address.';
        }

        return null;
    }

    /**
     * Resolve a host name to every address it currently points at.
     *
     * @return array<int, string>
     */
    protected static function resolve(string $host): array
    {
        $addresses = [];

        foreach (gethostbynamel($host) ?: [] as $address) {
            $addresses[] = $address;
        }

        // gethostbynamel() only returns IPv4, so ask for AAAA records too.
        if (function_exists('dns_get_record')) {
            foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (! empty($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($addresses));
    }

    /**
     * Is the address publicly routable (not loopback, private, link-local or
     * otherwise reserved)?
     */
    public static function isPublicAddress(string $address): bool
    {
        return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }
}
