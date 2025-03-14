<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Models\SentMessage;
use Http;

class TwitterService
{
    protected $twitter;

    public function __construct()
    {
        $this->twitter = new TwitterOAuth(
            config('services.twitter.api_key'),
            config('services.twitter.api_secret'),
            config('services.twitter.access_token'),
            config('services.twitter.access_secret')
        );
    }

    // Send Tweet Reply
    public function replyToMention($tweetId, $username)
    {
        $message = "@{$username} Love your furry VIP? 🐶 Join PAWS AI’s exclusive pet community! 3-day free trial 👉 [link]";

        return $this->twitter->post('statuses/update', [
            'status' => $message,
            'in_reply_to_status_id' => $tweetId
        ]);
    }

    // Send Direct Message
    public function sendDirectMessage($userId, $username)
    {
        /*if (SentMessage::where('user_id', $userId)->exists()) {
            return "Message already sent to {$username}";
        }*/

        $message = "Hey {$username}, ready to connect with pet lovers? Start your 3-day free PAWS AI trial: [link]";

        $payload = [
            'event' => [
                'type' => 'message_create',
                'message_create' => [
                    'target' => ['recipient_id' => $userId],
                    'message_data' => ['text' => $message]
                ]
            ]
        ];

        $response = $this->twitter->post('direct_messages/events/new', $payload);

        // Log the response from Twitter API
        \Log::info('Twitter DM API Response:', (array) $response);

        // Check if the message was successfully sent
        if (isset($response->errors)) {
            return response()->json(['error' => $response->errors]);
        }

        //SentMessage::create(['user_id' => $userId, 'username' => $username]);

        return response()->json(['success' => 'DM sent successfully']);
    }


    /*public function getUserIdByUsername($username)
    {
        $url = "https://api.twitter.com/2/users/by/username/{$username}";

        $response = Http::withToken(config('services.twitter.bearer_token'))
            ->get($url);

        if ($response->successful()) {
            return $response->json()['data']['id']; // Returns user ID
        }

        return null;
    }*/

    public function getUserIdByUsername($username)
    {
        $username = ltrim($username, '@'); // Remove '@' just in case
        $response = $this->twitter->get("users/by/username/{$username}");

        \Log::info('Twitter API Response:', (array) $response);

        return $response->data->id ?? null;
    }


    public function testTwitterConnection()
    {
        try {
            $response = $this->twitter->get('users/me');

            \Log::info('Twitter API Raw Response:', ['response' => $response]);
            // Check if response is empty
            if (!$response) {
                \Log::error('Twitter API returned an empty response.');
                return response()->json(['error' => 'Twitter API returned an empty response.']);
            }

            // Check if response contains errors
            if (isset($response->errors)) {
                \Log::error('Twitter API Error:', (array) $response->errors);
                return response()->json(['error' => $response->errors]);
            }

            // Log full response
            \Log::info('Twitter API Connection Test:', (array) $response);

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error('Twitter API Exception:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()]);
        }
    }





}
