<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\WhatsappService;

class WhatsappController extends Controller
{
      public function webhookVerify(Request $request)
    {
        if ($request->get('hub_verify_token') === config('whatsapp.verify_token')) {
            return response($request->get('hub_challenge'));
        }
        return response('Invalid verify token', 403);
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        // Handle incoming messages here
        logger('Incoming WhatsApp', $data);
        return response()->json(['status' => 'received']);
    }

    public function send(Request $request, WhatsappService $whatsapp)
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $whatsapp->sendMessage($request->to, $request->message);

        return response()->json($result);
    }
}
