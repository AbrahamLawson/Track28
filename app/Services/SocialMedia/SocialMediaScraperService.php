<?php

declare(strict_types=1);

namespace App\Services\SocialMedia;

use Illuminate\Support\Facades\Log;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Service to scrape social media pages and extract follower counts
 */
final class SocialMediaScraperService
{
    private HttpBrowser $browser;

    public function __construct()
    {
        $client = HttpClient::create([
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
            ],
        ]);
        $this->browser = new HttpBrowser($client);
    }

    /**
     * Scrape a social media URL and extract follower count
     */
    public function scrapeFollowerCount(string $url, string $platform): ?int
    {
        try {
            $crawler = $this->browser->request('GET', $url);

            return match (strtolower($platform)) {
                'instagram' => $this->extractInstagramFollowers($crawler, $url),
                'facebook' => $this->extractFacebookFollowers($crawler, $url),
                'tiktok' => $this->extractTikTokFollowers($crawler, $url),
                'twitter' => $this->extractTwitterFollowers($crawler, $url),
                'linkedin' => $this->extractLinkedInFollowers($crawler, $url),
                'youtube' => $this->extractYouTubeFollowers($crawler, $url),
                default => null,
            };
        } catch (\Exception $e) {
            Log::warning('Failed to scrape social media follower count', [
                'url' => $url,
                'platform' => $platform,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Scrape multiple social media accounts
     */
    public function scrapeMultipleSocialMedia(array $socialMediaList): array
    {
        $results = [];

        foreach ($socialMediaList as $social) {
            $followers = null;

            if (!empty($social['url']) && !empty($social['platform'])) {
                $followers = $this->scrapeFollowerCount($social['url'], $social['platform']);
            }

            $results[] = [
                'platform' => $social['platform'] ?? null,
                'url' => $social['url'] ?? null,
                'followers' => $followers,
            ];
        }

        return $results;
    }

    private function extractInstagramFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            // Try to extract from meta tags (og:description often contains follower count)
            $metaDescription = $crawler->filter('meta[property="og:description"]')->first();
            if ($metaDescription->count() > 0) {
                $description = $metaDescription->attr('content');
                if (preg_match('/(\d[\d,\.]*)\s*(M|K|Followers|followers)/i', $description, $matches)) {
                    return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
                }
            }

            // Try to extract from page content
            $htmlContent = $crawler->html();

            // Pattern for Instagram's JSON data
            if (preg_match('/"edge_followed_by":\{"count":(\d+)\}/', $htmlContent, $matches)) {
                return (int) $matches[1];
            }

            // Try alternative patterns
            if (preg_match('/(\d[\d,\.]+)\s*followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('Instagram scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractFacebookFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            // Try to find follower count in meta tags
            $metaDescription = $crawler->filter('meta[property="og:description"]')->first();
            if ($metaDescription->count() > 0) {
                $description = $metaDescription->attr('content');
                if (preg_match('/(\d[\d,\.]*)\s*(M|K|followers?|likes?)/i', $description, $matches)) {
                    return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
                }
            }

            // Try to find in page content
            if (preg_match('/(\d[\d,\.]+)\s*(followers?|likes?|people like this)/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('Facebook scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractTikTokFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            // TikTok often has follower count in meta tags or JSON-LD
            if (preg_match('/"followerCount":(\d+)/', $htmlContent, $matches)) {
                return (int) $matches[1];
            }

            if (preg_match('/(\d[\d,\.]*)\s*(M|K)?\s*Followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('TikTok scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractTwitterFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            // Twitter/X patterns
            if (preg_match('/"followers_count":(\d+)/', $htmlContent, $matches)) {
                return (int) $matches[1];
            }

            if (preg_match('/(\d[\d,\.]*)\s*(M|K)?\s*Followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('Twitter scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractLinkedInFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            if (preg_match('/(\d[\d,\.]*)\s*(M|K)?\s*followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('LinkedIn scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractYouTubeFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            // YouTube subscriber count
            if (preg_match('/"subscriberCountText":\{"simpleText":"([\d,\.]+[MK]?)\s*subscribers?"/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], '');
            }

            if (preg_match('/(\d[\d,\.]*)\s*(M|K)?\s*subscribers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('YouTube scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse follower count string and convert to integer
     */
    private function parseFollowerCount(string $count, string $suffix = ''): int
    {
        // Remove commas and spaces
        $count = str_replace([',', ' ', '.'], '', $count);

        // Check if suffix contains K or M
        $multiplier = 1;

        if (stripos($count, 'M') !== false || stripos($suffix, 'M') !== false) {
            $multiplier = 1000000;
            $count = str_replace(['M', 'm'], '', $count);
        } elseif (stripos($count, 'K') !== false || stripos($suffix, 'K') !== false) {
            $multiplier = 1000;
            $count = str_replace(['K', 'k'], '', $count);
        }

        // Convert to float first (in case of decimals like "1.5M")
        $value = floatval($count);

        return (int) ($value * $multiplier);
    }
}
