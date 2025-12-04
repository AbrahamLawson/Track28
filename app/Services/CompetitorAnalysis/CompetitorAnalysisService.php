<?php

declare(strict_types=1);

namespace App\Services\CompetitorAnalysis;

use App\DTOs\CompetitorDTO;
use App\DTOs\CompetitorAnalysisResultDTO;
use App\Exceptions\CompetitorAnalysisException;
use App\Services\OpenAI\OpenAIService;
use App\Services\SocialMedia\SocialMediaScraperService;
use Illuminate\Support\Facades\Log;

/**
 * Main service for competitor analysis
 */
final readonly class CompetitorAnalysisService
{
    public function __construct(
        private OpenAIService $openAIService,
        private SocialMediaScraperService $socialMediaScraperService,
    ) {
    }

    public function analyze(string $targetUrl): CompetitorAnalysisResultDTO
    {
        try {
            // Call OpenAI to search for competitors
            $jsonResponse = $this->openAIService->searchCompetitors($targetUrl);

            // Decode JSON
            $data = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new CompetitorAnalysisException(
                    'Failed to decode OpenAI response: ' . json_last_error_msg()
                );
            }

            // Transform data into DTOs
            $competitors = $this->transformToCompetitors($data['competitors'] ?? []);

            // Scrape real follower counts for each competitor's social media
            $competitors = $this->enrichWithRealFollowerCounts($competitors);

            return new CompetitorAnalysisResultDTO(
                targetUrl: $targetUrl,
                competitors: $competitors,
                totalFound: count($competitors),
            );
        } catch (\Exception $e) {
            Log::error('Error analyzing competitors', [
                'target_url' => $targetUrl,
                'error' => $e->getMessage(),
            ]);

            throw new CompetitorAnalysisException(
                'Unable to analyze competitors: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * @param array<array> $rawData
     * @return array<CompetitorDTO>
     */
    private function transformToCompetitors(array $rawData): array
    {
        return array_map(
            fn (array $data) => CompetitorDTO::fromArray($data),
            $rawData
        );
    }

    /**
     * Enrich competitors with real follower counts from social media scraping
     *
     * @param array<CompetitorDTO> $competitors
     * @return array<CompetitorDTO>
     */
    private function enrichWithRealFollowerCounts(array $competitors): array
    {
        $enrichedCompetitors = [];

        foreach ($competitors as $competitor) {
            // If competitor has social media, scrape real follower counts
            if (!empty($competitor->socialMedia)) {
                $scrapedSocialMedia = $this->socialMediaScraperService->scrapeMultipleSocialMedia(
                    $competitor->socialMedia
                );

                // Create new competitor DTO with scraped data
                $enrichedCompetitors[] = new CompetitorDTO(
                    name: $competitor->name,
                    productUrl: $competitor->productUrl,
                    price: $competitor->price,
                    description: $competitor->description,
                    positioning: $competitor->positioning,
                    strengths: $competitor->strengths,
                    notoriety: $competitor->notoriety,
                    socialMedia: $scrapedSocialMedia,
                );
            } else {
                // No social media, keep as is
                $enrichedCompetitors[] = $competitor;
            }
        }

        return $enrichedCompetitors;
    }
}
