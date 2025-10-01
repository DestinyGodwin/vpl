<?php

namespace App\Http\Controllers\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\v1\WhatsappService;

class WhatsappController extends Controller
{
      public function webhookVerify(Request $request)
    {
            logger()->info('Webhook Verify Request', $request->all());
        if ($request->get('hub_verify_token') === config('whatsapp.verify_token')) {
            return response($request->get('hub_challenge'));
        }
        return response('Invalid verify token', 403);
    }

    public function webhook(Request $request)
    {
            logger()->info('Webhook Incoming', $request->all());
        $data = $request->all();
        // Handle incoming messages here
        logger('Incoming WhatsApp', $data);
        return response()->json(['status' => 'received']);
    }

   public function send(Request $request, WhatsAppService $whatsapp)
{
    $request->validate([
        'to' => 'required|string',
        'message' => 'required|string',
        'image_url' => 'nullable|url',
    ]);

    $result = $whatsapp->sendMessage(
        $request->to,
        $request->message,
        $request->image_url
    );

    return response()->json($result);
}

}
