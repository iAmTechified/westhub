<?php

namespace App\Livewire\Admin;

use App\Models\Article;
use App\Models\Appointment;
use App\Models\GalleryItem;
use App\Models\JoinRequest;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public bool $readyToLoad = true;

    public array $metrics = [
        'published_articles' => 0,
        'pending_applications' => 0,
        'pending_appointments' => 0,
        'scheduled_articles' => 0,
        'published_gallery_items' => 0,
        'active_services' => 0,
    ];

    public array $quickLinks = [];
    public array $quickActions = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->can('access_articles')) {
            $this->quickLinks[] = ['label' => 'Create Article', 'route' => route('admin.articles.create'), 'icon' => 'plus'];
            $this->quickLinks[] = ['label' => 'Manage Articles', 'route' => route('admin.articles.index'), 'icon' => 'article'];
            $this->quickActions[] = ['label' => 'Draft New Article', 'route' => route('admin.articles.create'), 'icon' => 'plus'];
        }

        if ($user->can('access_applications')) {
            $this->quickLinks[] = ['label' => 'Review Applications', 'route' => route('admin.join-requests.index'), 'icon' => 'briefcase'];
        }

        if ($user->can('access_appointments')) {
            $this->quickLinks[] = ['label' => 'Manage Appointments', 'route' => route('admin.appointments.index'), 'icon' => 'calendar'];
            $this->quickActions[] = ['label' => 'Open Appointments', 'route' => route('admin.appointments.index'), 'icon' => 'calendar'];
        }

        if ($user->can('access_gallery')) {
            $this->quickLinks[] = ['label' => 'Update Gallery', 'route' => route('admin.gallery.index'), 'icon' => 'image'];
        }

        if ($user->can('access_care_services')) {
            $this->quickLinks[] = ['label' => 'Manage Care Services', 'route' => route('admin.care-services.index'), 'icon' => 'pulse'];
        }

        if ($user->can('access_locations')) {
            $this->quickActions[] = ['label' => 'Manage Locations', 'route' => route('admin.locations.index'), 'icon' => 'location'];
        }

        $this->quickActions[] = ['label' => 'Open Settings', 'route' => route('admin.settings.index'), 'icon' => 'settings'];
    }

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function render()
    {
        $user = Auth::user();
        $recentArticles = collect();
        $recentJoinRequests = collect();

        if ($this->readyToLoad) {
            $metricsData = [];
            
            if ($user->can('access_articles')) {
                $metricsData['published_articles'] = Article::published()->count();
                $metricsData['scheduled_articles'] = Article::where('status', Article::STATUS_SCHEDULED)->count();
                $recentArticles = Article::query()->latest('updated_at')->take(5)->get();
            }

            if ($user->can('access_applications')) {
                $metricsData['pending_applications'] = JoinRequest::where('status', JoinRequest::STATUS_NEW)->count();
                $recentJoinRequests = JoinRequest::query()->latest('created_at')->take(5)->get();
            }

            if ($user->can('access_appointments')) {
                $metricsData['pending_appointments'] = Appointment::whereIn('status', [Appointment::STATUS_NEW, Appointment::STATUS_CONFIRMED, Appointment::STATUS_RESCHEDULED])->count();
            }

            if ($user->can('access_gallery')) {
                $metricsData['published_gallery_items'] = GalleryItem::published()->count();
            }

            if ($user->can('access_care_services')) {
                $metricsData['active_services'] = Service::where('is_active', true)->count();
            }

            $this->metrics = array_merge($this->metrics, $metricsData);
        }

        return view('livewire.admin.dashboard', [
            'user' => $user,
            'metrics' => $this->metrics,
            'recentArticles' => $recentArticles,
            'recentJoinRequests' => $recentJoinRequests,
            'readyToLoad' => $this->readyToLoad,
            'quickLinks' => $this->quickLinks,
            'quickActions' => $this->quickActions,
        ])
            ->layout('layouts.admin');
    }
}
