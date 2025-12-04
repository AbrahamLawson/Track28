<?php

declare(strict_types=1);

namespace App\Commands;

/**
 * Command to analyze competitors for a target URL
 */
final readonly class AnalyzeCompetitorsCommand
{
    public function __construct(
        public string $targetUrl,
    ) {
    }
}
