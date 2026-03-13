<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWazuhAlert;  // <-- correct namespace
use Illuminate\Http\Request;

class WazuhWebhookController extends Controller
{
    public function receive(Request $request)
    {
        $payload = $request->all();

        ProcessWazuhAlert::dispatch($payload);

        return response()->json(['status' => 'queued'], 200);
    }
}
