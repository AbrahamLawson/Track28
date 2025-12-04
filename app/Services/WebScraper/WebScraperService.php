<?php

declare(strict_types=1);

namespace App\Services\WebScraper;

use Illuminate\Support\Facades\Log;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Service to scrape web pages and extract product information
 */
final class WebScraperService
{
    private HttpBrowser $browser;

    public function __construct()
    {
        $client = HttpClient::create([
            'timeout' => 10,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ],
        ]);
        $this->browser = new HttpBrowser($client);
    }

    /**
     * Scrape a URL and extract relevant product information
     */
    public function scrapeProductPage(string $url): array
    {
        try {
            $crawler = $this->browser->request('GET', $url);

            return [
                'title' => $this->extractTitle($crawler),
                'description' => $this->extractDescription($crawler),
                'price' => $this->extractPrice($crawler),
                'category' => $this->extractCategory($crawler),
                'meta_description' => $this->extractMetaDescription($crawler),
                'h1' => $this->extractH1($crawler),
                'product_type' => $this->extractProductType($crawler),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to scrape URL', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'title' => null,
                'description' => null,
                'price' => null,
                'category' => null,
                'meta_description' => null,
                'h1' => null,
                'product_type' => null,
            ];
        }
    }

    private function extractTitle(Crawler $crawler): ?string
    {
        try {
            $title = $crawler->filter('title')->first();
            return $title->count() > 0 ? trim($title->text()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractDescription(Crawler $crawler): ?string
    {
        try {
            // Try product description selectors
            $selectors = [
                '.product-description',
                '.description',
                '[itemprop="description"]',
                '.product__description',
                '#description',
                '.product-single__description',
            ];

            foreach ($selectors as $selector) {
                $element = $crawler->filter($selector)->first();
                if ($element->count() > 0) {
                    return trim($element->text());
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractPrice(Crawler $crawler): ?string
    {
        try {
            // Try various price selectors
            $selectors = [
                '[itemprop="price"]',
                '.price',
                '.product-price',
                '.product__price',
                'meta[property="product:price:amount"]',
                '[data-product-price]',
                '.money',
            ];

            foreach ($selectors as $selector) {
                $element = $crawler->filter($selector)->first();
                if ($element->count() > 0) {
                    // Check if it's a meta tag
                    if (str_starts_with($selector, 'meta')) {
                        return $element->attr('content');
                    }
                    return trim($element->text());
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractCategory(Crawler $crawler): ?string
    {
        try {
            // Try breadcrumb navigation
            $breadcrumb = $crawler->filter('.breadcrumb, .breadcrumbs, [aria-label="breadcrumb"]')->first();
            if ($breadcrumb->count() > 0) {
                return trim($breadcrumb->text());
            }

            // Try category meta tags
            $category = $crawler->filter('meta[property="product:category"]')->first();
            if ($category->count() > 0) {
                return $category->attr('content');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractMetaDescription(Crawler $crawler): ?string
    {
        try {
            $meta = $crawler->filter('meta[name="description"]')->first();
            if ($meta->count() > 0) {
                return $meta->attr('content');
            }

            // Try Open Graph description
            $ogDescription = $crawler->filter('meta[property="og:description"]')->first();
            if ($ogDescription->count() > 0) {
                return $ogDescription->attr('content');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractH1(Crawler $crawler): ?string
    {
        try {
            $h1 = $crawler->filter('h1')->first();
            return $h1->count() > 0 ? trim($h1->text()) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractProductType(Crawler $crawler): ?string
    {
        try {
            // Try product type meta tags
            $productType = $crawler->filter('meta[property="product:type"]')->first();
            if ($productType->count() > 0) {
                return $productType->attr('content');
            }

            // Try schema.org type
            $schemaType = $crawler->filter('[itemtype*="schema.org/Product"]')->first();
            if ($schemaType->count() > 0) {
                return 'Product';
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
