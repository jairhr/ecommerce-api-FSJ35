<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function createPaymentIntent(
        Request $request,
        Order $order
    ): JsonResponse {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to pay this order.',
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be paid.',
            ], 422);
        }

        try {
            $stripe = new StripeClient(
                config('services.stripe.secret')
            );

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => (int) round($order->total * 100),
                'currency' => 'usd',
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ],
            ]);

            $payment = Payment::updateOrCreate(
                [
                    'stripe_payment_intent_id' => $paymentIntent->id,
                ],
                [
                    'order_id' => $order->id,
                    'amount' => $order->total,
                    'currency' => 'usd',
                    'status' => 'pending',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment intent created successfully.',
                'data' => [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                ],
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to create Stripe payment.',
            ], 502);
        }
    }
}
