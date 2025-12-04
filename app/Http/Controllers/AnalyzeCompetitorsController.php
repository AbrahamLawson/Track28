<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Commands\AnalyzeCompetitorsCommand;
use App\Commands\Handlers\AnalyzeCompetitorsHandler;
use App\Exceptions\CompetitorAnalysisException;
use App\Http\Requests\AnalyzeCompetitorsRequest;
use Illuminate\Http\JsonResponse;

/**
 * Invokable controller to analyze competitors
 */
final class AnalyzeCompetitorsController extends Controller
{
    public function __construct(
        private readonly AnalyzeCompetitorsHandler $handler,
    ) {
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(AnalyzeCompetitorsRequest $request): JsonResponse
    {
        try {
            $command = new AnalyzeCompetitorsCommand(
                targetUrl: $request->validated('target_url'),
            );

            $result = $this->handler->handle($command);

            return response()->json([
                'success' => true,
                'data' => $result->toArray(),
            ]);
        } catch (CompetitorAnalysisException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
