<?php

namespace App\Livewire;

use App\Mail\EnquirySubmittedMail;
use App\Models\Appointment;
use App\Support\SupportContact;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Throwable;

class EnquiryForm extends Component
{
    public string $full_name = '';
    public string $email = '';
    public string $message = '';
    public string $honeypot = ''; // Spam protection

    protected $rules = [
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'message' => 'nullable|string|max:2000',
        'honeypot' => 'nullable|max:0', // Must be empty
    ];

    public function submit()
    {
        $this->validate();

        if (!empty($this->honeypot)) {
            return;
        }

        try {
            $enquiry = Appointment::query()->create([
                'full_name' => $this->full_name,
                'email' => $this->email,
                'message' => $this->message ?: null,
                'source' => 'website_enquiry',
                'status' => 'new',
                'meta' => [
                    'form' => 'enquiries.hero',
                    'ip' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 500),
                ],
            ]);

            $supportEmail = SupportContact::email();
            $fromEmail = \App\Support\SiteSettings::enquiriesEmail();
            $fromName = \App\Support\SiteSettings::fromName();

            $mailable = new EnquirySubmittedMail($enquiry);
            if (filled($fromEmail)) {
                $mailable->from($fromEmail, $fromName);
            }

            $pendingMail = Mail::to($enquiry->email);

            if ($supportEmail && strcasecmp($supportEmail, $enquiry->email) !== 0) {
                $pendingMail->cc($supportEmail);
            }

            $pendingMail->send($mailable);
        } catch (Throwable $exception) {
            report($exception);
            $this->addError('submit', 'We could not send your enquiry right now. Please try again.');
            return;
        }

        $this->reset(['full_name', 'email', 'message']);
        
        session()->flash('success', 'Thank you! We have received your enquiry.');
    }

    public function render()
    {
        return view('livewire.enquiry-form');
    }
}
