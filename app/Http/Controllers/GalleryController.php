<?php

namespace App\Http\Controllers;

use App\Support\FrontendContent;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $seo = [
            'title' => 'Our Gallery | WestHub Healthcare - Excellence in Home Care',
            'description' => 'Explore our gallery to see the compassionate care we provide at WestHub Healthcare. Our dedicated team ensures a safe and supportive environment for seniors in Illinois.',
        ];

        $images = FrontendContent::galleryImages();

        return view('pages.gallery.index', compact('seo', 'images'));
    }
}
