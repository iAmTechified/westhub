<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CareServicesController extends Controller
{
    /**
     * Handle the incoming request for Care Services page.
     */
    public function __invoke(Request $request): View
    {
        // Define SEO metadata for the Care Services page
        $seo = [
            'title' => 'Professional In-Home Care & Skilled Nursing | WestHub Healthcare',
            'description' => 'Discover our comprehensive in-home care services, including skilled nursing, clinical care, and therapeutic support. Licensed specialists providing care across Illinois.',
            'og_image' => asset('/assets/images/A_high-quality,_cinematic_202603201740 1.png'),
        ];

        return view('pages.care-services', compact('seo'));
    }
}
