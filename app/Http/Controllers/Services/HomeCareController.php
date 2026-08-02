<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeCareController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index()
    {
        // Define SEO metadata for this specific service
        $seo = [
            'title' => 'Home Care Services in Illinois | WestHub Healthcare',
            'description' => 'Compassionate non-medical home care services in Illinois. We offer personal hygiene support, companionship, and meal preparation for seniors.',
            'og_image' => asset('/assets/images/Scaled - women-holding-hands-medium-shot 1.png'),
        ];

        return view('pages.services.home-care', compact('seo'));
    }
}
