<?php

declare(strict_types=1);

namespace App\Commands\Handlers;

use App\Commands\AnalyzeCompetitorsCommand;
use App\DTOs\CompetitorAnalysisResultDTO;
use App\Services\CompetitorAnalysis\CompetitorAnalysisService;

/**
 * Handler for AnalyzeCompetitorsCommand
 */
final readonly class AnalyzeCompetitorsHandler
{
    public function __construct(
        private CompetitorAnalysisService $competitorAnalysisService,
    ) {
    }

    public function handle(AnalyzeCompetitorsCommand $command): CompetitorAnalysisResultDTO
    {
        return $this->competitorAnalysisService->analyze($command->targetUrl);
    }
}
