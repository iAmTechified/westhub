<?php

namespace App\Livewire\Admin\Concerns;

trait InteractsWithAdminToast
{
    protected function toast(string $message, string $type = 'info', ?string $title = null, int $duration = 4500): void
    {
        $normalizedType = match (strtolower(trim($type))) {
            'success' => 'success',
            'error', 'danger' => 'error',
            'warning', 'warn' => 'warning',
            default => 'info',
        };

        $safeDuration = max(1800, min($duration, 12000));

        $this->dispatch(
            'admin-toast',
            message: $message,
            type: $normalizedType,
            title: $title,
            duration: $safeDuration
        );
    }

    protected function toastSuccess(string $message, ?string $title = null, int $duration = 4200): void
    {
        $this->toast($message, 'success', $title, $duration);
    }

    protected function toastError(string $message, ?string $title = null, int $duration = 5400): void
    {
        $this->toast($message, 'error', $title, $duration);
    }

    protected function toastWarning(string $message, ?string $title = null, int $duration = 5000): void
    {
        $this->toast($message, 'warning', $title, $duration);
    }

    protected function toastInfo(string $message, ?string $title = null, int $duration = 4200): void
    {
        $this->toast($message, 'info', $title, $duration);
    }
}
