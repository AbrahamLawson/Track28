<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\WebScraper\WebScraperService;
use Tests\TestCase;

class WebScraperServiceTest extends TestCase
{
    public function test_can_scrape_basic_page_structure(): void
    {
        $scraper = new WebScraperService();

        // Test with a simple URL (you can replace this with your actual product URL)
        $url = 'https://example.com';

        $result = $scraper->scrapeProductPage($url);

        // Verify that the result is an array with the expected keys
        $this->assertIsArray($result);
        $this->assertArrayHasKey('title', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('price', $result);
        $this->assertArrayHasKey('category', $result);
        $this->assertArrayHasKey('meta_description', $result);
        $this->assertArrayHasKey('h1', $result);
        $this->assertArrayHasKey('product_type', $result);
    }
}
