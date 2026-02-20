<?php

use App\Http\Controllers\WebsiteController;
use App\Services\CongregationHubApi;
use App\Support\ChurchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home']);

Route::post('/api/clear-cache', function (Request $request) {
    $secret = $request->header('X-Cache-Secret');
    $domain = $request->header('X-Church-Domain');
    $configToken = config('website.api_token');

    // In single-tenant mode, validate the secret against the configured token
    if (! empty($configToken)) {
        if ($secret !== $configToken) {
            abort(403);
        }
        $context = new ChurchContext(token: $configToken);
    } elseif (! empty($domain)) {
        // In multi-tenant mode, use the domain from the header
        $context = new ChurchContext(domain: strtolower($domain));
    } else {
        abort(403, 'Cannot identify church for cache clearing');
    }

    app()->instance(ChurchContext::class, $context);
    app(CongregationHubApi::class)->clearCache();

    return response()->json(['ok' => true]);
});

Route::get('/{path}', [WebsiteController::class, 'page'])->where('path', '.*');
