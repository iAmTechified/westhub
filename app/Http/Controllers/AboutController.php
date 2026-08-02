<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $seo = [
            'title' => 'About WestHub Healthcare | Compassionate Home Care in Illinois',
            'description' => 'Learn about WestHub Healthcare, an Illinois-licensed and CHAP-certified home healthcare agency providing professional nursing and in-home care services.',
            'og_image' => asset('assets/images/lady thumbs up.png'),
        ];

        return view('pages.about', compact('seo'));
    }
}
