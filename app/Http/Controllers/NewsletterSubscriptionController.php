<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterWelcomeMail;
use App\Models\Subscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $email = Str::lower(trim($validated['email']));

        $subscriber = Subscriber::query()->updateOrCreate(
            ['email' => $email],
            [
                'source' => 'website',
                'status' => Subscriber::STATUS_SUBSCRIBED,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
                'meta' => [
                    'ip' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                ],
            ]
        );

        if ($subscriber->wasRecentlyCreated) {
            try {
                Mail::to($subscriber->email)->queue(new NewsletterWelcomeMail($subscriber));
            } catch (\Throwable $e) {
                // Silently fail mail delivery to not block the user experience
                \Illuminate\Support\Facades\Log::error('Newsletter welcome email failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => $subscriber->wasRecentlyCreated
                ? 'Thanks for subscribing. A welcome email has been sent.'
                : 'You are already on our newsletter list.',
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->query('email');
        if (!$email) {
            return redirect('/')->with('error', 'Invalid unsubscribe link.');
        }

        $subscriber = Subscriber::where('email', $email)->first();
        if ($subscriber) {
            $subscriber->update([
                'status' => Subscriber::STATUS_UNSUBSCRIBED,
                'unsubscribed_at' => now(),
            ]);
        }

        // Return a simple view or redirect with message
        return view('pages.unsubscribe-success');
    }
}
