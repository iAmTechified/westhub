<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // Define SEO metadata
        $seo = [
            'title' => 'WestHub Healthcare | Compassionate Home Care & Nursing in Illinois',
            'description' => 'Professional in-home care, nursing services, and therapeutic support in Illinois. CHAP certified agency providing personalized care for seniors and families.',
            'og_image' => asset('/assets/images/Slide Image 1.jpg'),
        ];

        return view('pages.home', compact('seo'));
    }
}
