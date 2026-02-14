<?php

namespace App\Http\Controllers;

use App\Services\CongregationHubApi;
use App\Support\DataTransformer;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function __construct(
        protected CongregationHubApi $api,
        protected DataTransformer $transformer,
    ) {}

    public function home()
    {
        $data = $this->api->homepage();
        $viewData = $this->transformer->transformHomepage($data);
        $theme = $data['theme']['name'] ?? config('website.theme');

        return $this->render("themes::pages.{$theme}.home", $viewData);
    }

    public function page(Request $request, string $path)
    {
        $query = $request->only(['year', 'month', 'search', 'speaker', 'series', 'book', 'page']);
        $data = $this->api->page($path, $query);
        $status = $data['_status'] ?? 200;
        $template = $data['_template'] ?? 'page';
        $theme = $data['theme']['name'] ?? config('website.theme');

        [$viewName, $viewData] = $this->transformer->transformPage($template, $path, $data);

        return $this->render("themes::pages.{$theme}.{$viewName}", $viewData, $status);
    }

    protected function render(string $themeView, array $viewData, int $status = 200)
    {
        return response()->view('layouts.app', [
            'themeView' => $themeView,
            'themeData' => $viewData,
            'siteTitle' => $viewData['siteTitle'] ?? null,
            'siteName' => $viewData['siteName'] ?? null,
        ], $status);
    }
}
