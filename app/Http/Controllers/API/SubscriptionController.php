<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class SubscriptionController extends Controller
{
    use ResponseTrait;

    // Create subscription endpoint
    public function createSubscription(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.key.secret'));
        $user = auth()->user();

        $validation = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id',
        ]);
        if ($validation->fails()) {
            return $this->sendError('Validation failed', $validation->errors()->toArray(), 422);
        }

        $plan = $request->plan_id;
        $plan = Plan::find($plan);

        if (! $plan) {
            return $this->sendError('Plan not found', [], 404);
        }

        if($user->subscribed('default')) {
            return $this->sendError('User already has a subscription', [], 400);
        }


        try {
            // Create Customer
            $stripeCustomer = $user->createOrGetStripeCustomer();

            // Update customer id
            $user->update([
                'stripe_id' => $stripeCustomer->id,
            ]);

            // Create a new Checkout Session
            $checkoutSession = Session::create([
                'payment_method_types' => ['card'],
                'customer'             => $stripeCustomer->id,
                'line_items'           => [
                    [
                        'price'    => Crypt::decrypt($plan->stripe_price_id),
                        'quantity' => 1,
                    ],
                ],
                'mode'                 => 'subscription',
                'success_url'          => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'           => route('checkout.cancel'),
            ]);

            return response()->json([
                'checkout_url' => $checkoutSession->url, // Provide the Stripe Checkout URL
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Stripe Checkout session creation failed: ' . $e->getMessage(),
            ], 500);
        }

    }

    // Cancel subscription endpoint
    public function cancelSubscription(Request $request)
    {
        $user = auth()->user();

        // Ensure the user has a subscription
        if ($user->subscribed('default')) {
            $user->subscription('default')->cancel();
            return response()->json(['message' => 'Subscription successfully canceled!'], 200);
        }

        return response()->json(['error' => 'No active subscription found.'], 404);
    }

    public function checkoutSuccess(Request $request)
    {
        
    }
}
