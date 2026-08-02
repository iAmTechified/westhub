<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PrivacyPolicyController extends Controller
{
    /**
     * Display the privacy policy page.
     */
    public function __invoke(Request $request): View
    {
        $seo = [
            'title' => 'Privacy Policy | WestHub Healthcare',
            'description' => 'Read WestHub Healthcare\'s privacy policy to understand how we collect, use, and protect your personal information.',
            'og_image' => asset('/assets/images/Slide Image 1.jpg'),
        ];

        return view('pages.privacy-policy', compact('seo'));
    }
}
