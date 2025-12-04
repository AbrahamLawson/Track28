<?php

declare(strict_types=1);

use App\Http\Controllers\AnalyzeCompetitorsController;
use Illuminate\Support\Facades\Route;

Route::post('/competitors/analyze', AnalyzeCompetitorsController::class)
    ->name('competitors.analyze');
