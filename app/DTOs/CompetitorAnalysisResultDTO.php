<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Result of competitor analysis
 */
final readonly class CompetitorAnalysisResultDTO
{
    /**
     * @param array<CompetitorDTO> $competitors
     */
    public function __construct(
        public string $targetUrl,
        public array $competitors,
        public int $totalFound,
    ) {
    }

    public function toArray(): array
    {
        return [
            'target_url' => $this->targetUrl,
            'total_found' => $this->totalFound,
            'competitors' => array_map(
                fn (CompetitorDTO $competitor) => $competitor->toArray(),
                $this->competitors
            ),
        ];
    }
}
