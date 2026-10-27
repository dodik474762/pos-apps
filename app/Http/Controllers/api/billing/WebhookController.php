<?php

namespace App\Http\Controllers\api\billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(private WebhookService $webhooks) {}

    public function handle(Request $request, string $gateway)
    {
        $result = $this->webhooks->handle($request, $gateway);

        return response()->json($result['body'], $result['http']);
    }
}
