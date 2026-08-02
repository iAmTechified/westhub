<?php

namespace Tests\Feature;

use App\Support\LocationData;
use Tests\TestCase;

class LocationPageTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_barrington_township_is_accessible(): void
    {
        $response = $this->get('/locations/cook-county/barrington-township');

        $response->assertStatus(200);
        $response->assertSee('Barrington Township');
        $response->assertSee('WestHub Healthcare Services');
        $response->assertSee('In-Home Skilled Nurses');
    }

    public function test_invalid_location_returns_404(): void
    {
        $response = $this->get('/locations/cook-county/invalid-township');

        $response->assertStatus(404);
    }

    public function test_service_locations_are_complete_and_deduplicated(): void
    {
        $names = array_column(LocationData::serviceLocationLinks(), 'name');

        $this->assertCount(99, $names);
        $this->assertSame($names, array_values(array_unique($names)));
        $this->assertContains('Addison', $names);
        $this->assertContains('Du Page County', $names);
        $this->assertContains('Grundy County & surrounding areas', $names);
        $this->assertContains('Zion', $names);
        $this->assertSame(1, collect($names)->filter(fn (string $name): bool => $name === 'Buffalo Grove')->count());
    }
}
