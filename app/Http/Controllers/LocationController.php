<?php

namespace App\Http\Controllers;

use App\Support\LocationData;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Handle the dynamic township requests.
     */
    public function __invoke(string $county, string $township): View
    {
        $location = LocationData::getTownship($county, $township);

        if (!$location) {
            abort(404);
        }

        $seo = [
            'title' => $location->meta_title,
            'description' => $location->meta_description,
        ];

        return view('pages.location', compact('location', 'seo'));
    }

    /**
     * Handle the dynamic county landing pages.
     */
    public function countyShow(string $countySlug): View
    {
        $countyData = LocationData::get($countySlug);

        if (!$countyData) {
            abort(404);
        }

        $seo = [
            'title' => $countyData['meta_title'] ?? "Home Care & Nursing Services in {$countyData['name']} | WestHub Healthcare",
            'description' => $countyData['meta_description'] ?? "Trusted home healthcare services across {$countyData['name']}. {$countyData['subtitle']} serving all townships.",
            'og_image' => asset('/assets/images/Location image.png'),
        ];

        return view('pages.locations.county', compact('countyData', 'seo', 'countySlug'));
    }
}
