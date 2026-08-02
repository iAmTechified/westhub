<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Display the therapeutic services page.
     */
    public function therapeutic(): View
    {
        $seo = [
            'title' => 'Therapeutic Services | Physical & Occupational Therapy | WestHub Healthcare',
            'description' => 'Professional In-Home Physical, Occupational, and Speech Therapy. Our expert therapists deliver high-quality therapeutic care right to your doorstep.',
        ];

        return view('pages.services.therapeutic-services', compact('seo'));
    }

    /**
     * Display the nursing care services page.
     */
    public function nursingCare(): View
    {
        return view('pages.services.nursing-care');
    }
}
