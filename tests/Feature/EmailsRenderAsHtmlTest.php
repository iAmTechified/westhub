<?php

namespace Tests\Feature;

use App\Mail\NewsletterWelcomeMail;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * These templates are hand-written HTML. Rendered as Markdown mail, every line
 * indented by four spaces became a code block and subscribers received the
 * source of the email instead of the email.
 */
class EmailsRenderAsHtmlTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_newsletter_welcome_arrives_as_markup_not_as_source(): void
    {
        $subscriber = Subscriber::query()->create([
            'email' => 'ada@example.com',
            'full_name' => 'Ada Client',
            'status' => Subscriber::STATUS_SUBSCRIBED,
            'subscribed_at' => now(),
        ]);

        $html = (new NewsletterWelcomeMail($subscriber))->render();

        // Real markup...
        $this->assertStringContainsString('<div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%)', $html);
        $this->assertStringContainsString('Welcome to our community!', $html);

        // ...and none of it shown as text.
        $this->assertStringNotContainsString('&lt;div', $html);
        $this->assertStringNotContainsString('<pre>', $html);
        $this->assertStringNotContainsString('<code>', $html);
    }

    public function test_the_welcome_email_still_carries_a_working_unsubscribe_link(): void
    {
        $subscriber = Subscriber::query()->create([
            'email' => 'ada@example.com',
            'status' => Subscriber::STATUS_SUBSCRIBED,
            'subscribed_at' => now(),
        ]);

        $html = (new NewsletterWelcomeMail($subscriber))->render();

        $this->assertStringContainsString(route('newsletter.unsubscribe', ['email' => 'ada@example.com']), $html);
        $this->assertStringContainsString(config('app.url'), $html);
    }
}
