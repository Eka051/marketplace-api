<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentRedirectController extends Controller
{
    /**
     * Payment success redirect
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        return view('payment.finish', [
            'status' => 'success',
            'order_id' => $orderId,
            'message' => 'Payment successful!'
        ]);
    }

    /**
     * Payment unfinish redirect
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->get('order_id');
        
        return view('payment.unfinish', [
            'status' => 'pending',
            'order_id' => $orderId,
            'message' => 'Payment not completed. Please try again.'
        ]);
    }

    /**
     * Payment error redirect
     */
    public function error(Request $request)
    {
        $orderId = $request->get('order_id');
        
        return view('payment.error', [
            'status' => 'failed',
            'order_id' => $orderId,
            'message' => 'Payment failed. Please try again.'
        ]);
    }
}