<?php

use App\Http\Controllers\WebsiteController;
use App\Services\CongregationHubApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home']);

Route::post('/api/clear-cache', function (Request $request) {
    if ($request->header('X-Cache-Secret') !== config('website.api_token')) {
        abort(403);
    }

    app(CongregationHubApi::class)->clearCache();

    return response()->json(['ok' => true]);
});

Route::get('/{path}', [WebsiteController::class, 'page'])->where('path', '.*');
