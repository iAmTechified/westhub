<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnquiriesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $seo = [
            'title' => 'Contact WestHub Healthcare | Senior Care Enquiries in Illinois',
            'description' => 'Get in touch with WestHub Healthcare for the best senior care services in Illinois. Contact us for home nursing, therapeutic services, and personalized homecare.',
            'og_image' => asset('/assets/images/3b433c0af15ca9c886037e29cb5c3e66edb201f9.png'),
        ];

        return view('pages.enquiries', compact('seo'));
    }
}
