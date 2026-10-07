<?php

namespace App\Http\Controllers;

use App\Support\ChurchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index(ChurchContext $church)
    {
        return $this->forward($church, 'GET', '/chat/status');
    }

    public function store(Request $request, ChurchContext $church)
    {
        $data = $request->validate(['question' => 'required|string|min:3|max:800']);

        return $this->forward($church, 'POST', '/chat', $data);
    }

    private function forward(ChurchContext $church, string $method, string $path, array $data = [])
    {
        try {
            $http = Http::acceptJson()->connectTimeout(5)->timeout(90);
            $http = $church->token ? $http->withToken($church->token) : $http->withHeaders(['X-Church-Domain' => $church->domain]);
            $url = rtrim(config('website.api_url'), '/').'/api/website'.$path;
            $r = $method === 'GET' ? $http->get($url) : $http->post($url, $data);
            if (! $r->successful()) {
                return response()->json(['message' => __('chatbot.unavailable_now')], in_array($r->status(), [404, 422, 429]) ? $r->status() : 503);
            }

            return response()->json($r->json())->header('Cache-Control', 'no-store');
        } catch (\Throwable) {
            return response()->json(['message' => __('chatbot.unavailable_now')], 503);
        }
    }
}
