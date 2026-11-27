<?php

namespace App\Http\Controllers;

use App\Services\StripeOrderProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeOrderProcessor $orders): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            return response('Stripe webhook is not configured.', 503);
        }

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                (string) $request->header('Stripe-Signature'),
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid Stripe webhook signature.', 400);
        }

        $session = $event->data->object;

        match ($event->type) {
            'checkout.session.completed', 'checkout.session.async_payment_succeeded' => $orders->markPaid($session),
            'checkout.session.async_payment_failed' => $orders->markFailed($session, 'failed'),
            'checkout.session.expired' => $orders->markFailed($session, 'expired'),
            default => null,
        };

        return response('Webhook handled.', 200);
    }
}
