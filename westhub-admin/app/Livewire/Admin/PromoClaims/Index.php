<?php

namespace App\Livewire\Admin\PromoClaims;

use App\Jobs\Promotions\AppendPromoClaimToGoogleSheet;
use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\PromoClaim;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * Deliberately thin. The promo runs itself: vouchers are emailed automatically
 * and claims sync to the sheet on their own. This screen exists for the days
 * someone wants to check numbers, record a follow-up call, or export the list.
 */
class Index extends Component
{
    use WithPagination;
    use InteractsWithAdminToast;

    protected $paginationView = 'livewire.admin-pagination';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $status = 'all';


    public function mount(): void
    {
        Gate::authorize('promos.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, array_merge(['all'], PromoClaim::STATUSES), true) ? $status : 'all';
        $this->resetPage();
    }

    public function markContacted(int $claimId): void
    {
        Gate::authorize('promos.manage');

        $claim = PromoClaim::query()->findOrFail($claimId);

        if ($claim->status === PromoClaim::STATUS_NEW) {
            $claim->forceFill([
                'status' => PromoClaim::STATUS_CONTACTED,
                'contacted_at' => now(),
                'assigned_to' => auth()->id(),
            ])->save();

            $this->syncStatus($claim);
        }

        $this->toastSuccess('Marked as contacted.', 'Promo Claims');
    }

    public function markRedeemed(int $claimId): void
    {
        Gate::authorize('promos.manage');

        $claim = PromoClaim::query()->findOrFail($claimId);

        $claim->forceFill([
            'status' => PromoClaim::STATUS_REDEEMED,
            'redeemed_at' => $claim->redeemed_at ?? now(),
        ])->save();

        $this->syncStatus($claim);
        $this->toastSuccess('Marked as redeemed.', 'Promo Claims');
    }

    public function cancelClaim(int $claimId): void
    {
        Gate::authorize('promos.manage');

        $claim = PromoClaim::query()->findOrFail($claimId);

        $claim->forceFill(['status' => PromoClaim::STATUS_CANCELLED])->save();

        $this->syncStatus($claim);
        $this->toastSuccess('Claim cancelled.', 'Promo Claims');
    }

    public function resendVoucher(int $claimId): void
    {
        Gate::authorize('promos.manage');

        $claim = PromoClaim::query()->with('service')->findOrFail($claimId);

        try {
            Mail::to($claim->email)->send(new \App\Mail\PromoVoucherIssued($claim, \App\Support\PromoOffer::fromSettings()));
            $claim->forceFill(['emailed_at' => now()])->save();
            $this->toastSuccess('Voucher email resent to ' . $claim->email . '.', 'Promo Claims');
        } catch (Throwable $e) {
            report($e);
            $this->toastError('Could not send the email: ' . $e->getMessage(), 'Promo Claims');
        }
    }

    public function export(): StreamedResponse
    {
        Gate::authorize('promos.export');

        $rows = $this->baseQuery()->with('service')->get();
        $filename = 'promo-claims-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, ['ID', 'Claimed at', 'Name', 'Email', 'Phone', 'Service', 'Voucher', 'Status', 'Expires', 'Redeemed at', 'Appointment', 'Consent']);

            foreach ($rows as $claim) {
                fputcsv($handle, [
                    $claim->id,
                    $claim->created_at?->toDateTimeString(),
                    $claim->full_name,
                    $claim->email,
                    $claim->phone,
                    $claim->service?->name,
                    $claim->voucher_code,
                    $claim->status,
                    $claim->expires_at?->toDateString(),
                    $claim->redeemed_at?->toDateTimeString(),
                    $claim->appointment_id,
                    $claim->consent_at ? 'yes' : 'no',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    protected function syncStatus(PromoClaim $claim): void
    {
        try {
            AppendPromoClaimToGoogleSheet::dispatch($claim->id, true);
        } catch (Throwable $e) {
            report($e);
        }
    }


    protected function baseQuery()
    {
        return PromoClaim::query()
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query): void {
                $term = '%' . trim($this->search) . '%';

                $query->where(function ($inner) use ($term): void {
                    $inner->where('full_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                        ->orWhere('voucher_code', 'like', $term);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function render()
    {
        $offer = \App\Support\PromoOffer::fromSettings();

        $counts = PromoClaim::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.admin.promo-claims.index', [
            'claims' => $this->baseQuery()->with('service')->paginate(20),
            'statuses' => PromoClaim::STATUSES,
            'counts' => $counts,
            'totalClaims' => (int) $counts->sum(),
            'redeemedCount' => (int) ($counts[PromoClaim::STATUS_REDEEMED] ?? 0),
            'offer' => $offer,
            'sheetsEnabled' => SiteSettings::bool('integrations', 'google_sheets_enabled', false),
        ])->layout('layouts.admin');
    }
}
