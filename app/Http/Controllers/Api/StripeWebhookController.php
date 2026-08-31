<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

#[OA\Tag(
    name: 'Stripe Webhook',
    description: 'Stripe webhook operations'
)]
class StripeWebhookController extends Controller
{
    #[OA\Post(
        path: '/api/stripe/webhook',
        summary: 'Handle Stripe webhook',
        description: 'Receives and processes Stripe payment events.',
        tags: ['Stripe Webhook'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    type: 'object'
                )
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'Stripe-Signature',
                description: 'Stripe webhook signature used to verify the request.',
                in: 'header',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 't=1672531200,v1=example_signature'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Webhook received successfully'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid payload or signature'
            )
        ]
    )]
    public function handle(Request $request): Response
    {
        $payload = $request->getContent();

        $signature = $request->header('Stripe-Signature');

        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $webhookSecret
            );
        } catch (UnexpectedValueException $e) {

            return response('Invalid payload', 400);

        } catch (SignatureVerificationException $e) {

            return response('Invalid signature', 400);
        }

        switch ($event->type) {

            case 'payment_intent.succeeded':

                $paymentIntent = $event->data->object;

                $this->handlePaymentSucceeded($paymentIntent);

                break;

            case 'payment_intent.payment_failed':

                $paymentIntent = $event->data->object;

                $this->handlePaymentFailed($paymentIntent);

                break;
        }

        return response('Webhook received', 200);
    }

    private function handlePaymentSucceeded($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {

            $payment = Payment::where(
                'stripe_payment_intent_id',
                $paymentIntent->id
            )->first();

            if (!$payment) {
                return;
            }

            $payment->update([
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            $payment->order->update([
                'status' => 'paid',
            ]);
        });
    }

    private function handlePaymentFailed($paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {

            $payment = Payment::where(
                'stripe_payment_intent_id',
                $paymentIntent->id
            )->first();

            if (!$payment) {
                return;
            }

            $payment->update([
                'status' => 'failed',
            ]);

            $payment->order->update([
                'status' => 'failed',
            ]);
        });
    }
}
