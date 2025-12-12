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
            Log::info('Starting social media scraping', [
                'url' => $url,
                'platform' => $platform,
            ]);

            $crawler = $this->browser->request('GET', $url);

            $result = match (strtolower($platform)) {
                'instagram' => $this->extractInstagramFollowers($crawler, $url),
                'facebook' => $this->extractFacebookFollowers($crawler, $url),
                'tiktok' => $this->extractTikTokFollowers($crawler, $url),
                'twitter' => $this->extractTwitterFollowers($crawler, $url),
                'linkedin' => $this->extractLinkedInFollowers($crawler, $url),
                'youtube' => $this->extractYouTubeFollowers($crawler, $url),
                default => null,
            };

            Log::info('Social media scraping completed', [
                'url' => $url,
                'platform' => $platform,
                'followers' => $result,
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to scrape social media follower count', [
                'url' => $url,
                'platform' => $platform,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
            Log::info('Extracting Instagram followers', ['url' => $url]);

            // Try to extract from meta tags (og:description often contains follower count)
            $metaDescription = $crawler->filter('meta[property="og:description"]')->first();
            if ($metaDescription->count() > 0) {
                $description = $metaDescription->attr('content');
                Log::info('Instagram meta description found', ['description' => $description]);

                if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|Followers|followers)/i', $description, $matches)) {
                    Log::info('Instagram meta regex matched', [
                        'full_match' => $matches[0],
                        'count' => $matches[1],
                        'suffix' => $matches[2] ?? '',
                    ]);
                    return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
                }
            }

            // Try to extract from page content
            $htmlContent = $crawler->html();

            // Pattern for Instagram's JSON data
            if (preg_match('/"edge_followed_by":\{"count":(\d+)\}/', $htmlContent, $matches)) {
                Log::info('Instagram JSON data matched', ['count' => $matches[1]]);
                return (int) $matches[1];
            }

            // Try alternative patterns
            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|followers?)/i', $htmlContent, $matches)) {
                Log::info('Instagram content regex matched', [
                    'full_match' => $matches[0],
                    'count' => $matches[1],
                    'suffix' => $matches[2] ?? '',
                ]);
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            Log::warning('Instagram followers not found in page', ['url' => $url]);
            return null;
        } catch (\Exception $e) {
            Log::error('Instagram scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractFacebookFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            Log::info('Extracting Facebook followers', ['url' => $url]);

            // Try to find follower count in meta tags
            $metaDescription = $crawler->filter('meta[property="og:description"]')->first();
            if ($metaDescription->count() > 0) {
                $description = $metaDescription->attr('content');
                Log::info('Facebook meta description found', ['description' => $description]);

                if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B|followers?|likes?)/i', $description, $matches)) {
                    Log::info('Facebook meta regex matched', [
                        'full_match' => $matches[0],
                        'count' => $matches[1],
                        'suffix' => $matches[2] ?? '',
                    ]);
                    return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
                }
            }

            // Try to find in page content
            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B|followers?|likes?|people like this)/i', $htmlContent, $matches)) {
                Log::info('Facebook content regex matched', [
                    'full_match' => $matches[0],
                    'count' => $matches[1],
                    'suffix' => $matches[2] ?? '',
                ]);
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            Log::warning('Facebook followers not found in page', ['url' => $url]);
            return null;
        } catch (\Exception $e) {
            Log::error('Facebook scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
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

            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B)?\s*Followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('TikTok scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
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

            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B)?\s*Followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Twitter scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractLinkedInFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B)?\s*followers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LinkedIn scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function extractYouTubeFollowers(Crawler $crawler, string $url): ?int
    {
        try {
            $htmlContent = $crawler->html();

            // YouTube subscriber count
            if (preg_match('/"subscriberCountText":\{"simpleText":"(\d+(?:[,\.]\d+)*)\s*([MKB])?\s*subscribers?"/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            if (preg_match('/(\d+(?:[,\.]\d+)*)\s*(M|K|B)?\s*subscribers?/i', $htmlContent, $matches)) {
                return $this->parseFollowerCount($matches[1], $matches[2] ?? '');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('YouTube scraping failed', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse follower count string and convert to integer
     */
    private function parseFollowerCount(string $count, string $suffix = ''): int
    {
        $originalCount = $count;
        $originalSuffix = $suffix;

        // Check if suffix contains K, M, or B
        $multiplier = 1;
        $hasSuffix = false;

        if (stripos($count, 'M') !== false || stripos($suffix, 'M') !== false) {
            $multiplier = 1000000;
            $count = str_replace(['M', 'm'], '', $count);
            $hasSuffix = true;
        } elseif (stripos($count, 'K') !== false || stripos($suffix, 'K') !== false) {
            $multiplier = 1000;
            $count = str_replace(['K', 'k'], '', $count);
            $hasSuffix = true;
        } elseif (stripos($count, 'B') !== false || stripos($suffix, 'B') !== false) {
            $multiplier = 1000000000;
            $count = str_replace(['B', 'b'], '', $count);
            $hasSuffix = true;
        }

        // Clean the number
        // Remove spaces
        $count = str_replace(' ', '', $count);

        // If there's a suffix (K, M, B), treat dots and commas as decimal separators
        // Example: "1.5M" = 1.5 million, "2,5K" = 2.5 thousand
        if ($hasSuffix) {
            // Replace comma with dot for decimal (European format)
            $count = str_replace(',', '.', $count);
            // Keep only the last dot (in case of multiple)
            $parts = explode('.', $count);
            if (count($parts) > 1) {
                $last = array_pop($parts);
                $count = implode('', $parts) . '.' . $last;
            }
        } else {
            // No suffix: dots and commas are thousand separators, remove them
            // Example: "150,000" = 150000, "150.000" = 150000
            $count = str_replace([',', '.'], '', $count);
        }

        // Convert to float first (in case of decimals like "1.5M")
        $value = floatval($count);
        $result = (int) ($value * $multiplier);

        Log::info('Parsing follower count', [
            'original_count' => $originalCount,
            'original_suffix' => $originalSuffix,
            'has_suffix' => $hasSuffix,
            'multiplier' => $multiplier,
            'cleaned_count' => $count,
            'float_value' => $value,
            'final_result' => $result,
        ]);

        return $result;
    }
}
