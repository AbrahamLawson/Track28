<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Services\WebScraper\WebScraperService;

// Create the scraper instance
$scraper = new WebScraperService();

// Test URL (you can change this to your actual product URL)
$url = $argv[1] ?? 'https://www.qwetch.com/fr/products/bouteille-isotherme-en-inox-500ml-granite-noir';

echo "Scraping URL: {$url}\n\n";

$result = $scraper->scrapeProductPage($url);

echo "Results:\n";
echo "========\n\n";

foreach ($result as $key => $value) {
    if ($value !== null) {
        echo "{$key}: {$value}\n\n";
    } else {
        echo "{$key}: (not found)\n\n";
    }
}
