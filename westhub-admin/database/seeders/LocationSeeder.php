<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\Location;
use App\Models\Township;
use App\Support\LocationData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCountiesAndTownships();
        $this->seedLegacyLocationContent();
    }

    private function seedCountiesAndTownships(): void
    {
        try {
            $schema = Schema::connection((new County())->getConnectionName());

            if (! $schema->hasTable('counties') || ! $schema->hasTable('townships')) {
                return;
            }

            foreach (LocationData::seedableCounties() as $countySlug => $countyData) {
                $county = County::query()->firstOrCreate(
                    ['slug' => $countySlug],
                    [
                        'name' => $countyData['name'],
                        'description' => $countyData['subtitle'] ?? null,
                        'meta_title' => $countyData['meta_title'] ?? null,
                        'meta_description' => $countyData['meta_description'] ?? null,
                        'is_active' => true,
                        'sort_order' => $countyData['sort_order'] ?? 0,
                    ],
                );

                foreach ($countyData['townships'] ?? [] as $township) {
                    Township::query()->firstOrCreate(
                        [
                            'county_id' => $county->id,
                            'slug' => $township['slug'],
                        ],
                        [
                            'name' => $township['name'],
                            'content' => null,
                            'meta_title' => null,
                            'meta_description' => null,
                            'is_active' => true,
                            'sort_order' => $township['sort_order'] ?? 0,
                        ],
                    );
                }
            }
        } catch (Throwable) {
            //
        }
    }

    private function seedLegacyLocationContent(): void
    {
        try {
            if (! Schema::connection((new Location())->getConnectionName())->hasTable('locations')) {
                return;
            }

            Location::query()->firstOrCreate(
                [
                    'county_slug' => 'cook-county',
                    'township_slug' => 'barrington-township',
                ],
                [
                    'county_name' => 'Cook County',
                    'township_name' => 'Barrington Township',
                    'meta_title' => 'Best In-Home Care & Senior Services in Barrington Township, Cook County | WestHub Healthcare',
                    'meta_description' => 'Looking for professional in-home skilled nursing or caregiving in Barrington Township? WestHub Healthcare provides top-rated home care, homemaking, and aide services.',
                    'content' => [
                        'hero' => [
                            'title' => 'WestHub Healthcare Services in Barrington Township',
                            'cta_text' => 'Book Appointment',
                        ],
                        'details' => [
                            'title' => 'Barrington Township In-Home Skilled Nurses, Caregivers & Home Healthcare Aides',
                            'subtitle' => 'Meal Preparation, Light Housekeeping, Bathing & Dressing, Medication Reminders, Incontinence, Alzheimer\'s/Dementia, Respite care, and Errands.',
                            'mission' => 'Our Mission Statement is to be a top-rated home healthcare service provider in Barrington Township by using excellent human skills and technology to differentiate our services, thereby continually seeking development of our staff and processes based on current evidence-based industry techniques for the most creative, innovative, and personalized homecare experiences that promote the overall health, wellbeing, and independence our clients and patients',
                            'secondary_title' => 'Caregivers Rendering Exceptional Home and senior care services in Barrington Township',
                            'secondary_description' => 'At WestHub Healthcare, we believe that the best place to heal and thrive is at home. As Barrington Township\'s trusted choice for professional home health care, our mission is to support your wellbeing through high-quality services tailored to your life.',
                            'promise' => 'We prioritize your comfort above all else. Our team of compassionate experts works closely with you to deliver care that isn\'t just professional; it\'s personal. Experience the peace of mind that comes with a team dedicated to your unique journey.',
                        ],
                        'offerings' => [
                            'title' => 'Our Offerings: Senior Home Care in Barrington Township',
                            'items' => [
                                [
                                    'title' => 'Your Trusted Choice for Care in Barrington Township',
                                    'description' => 'At WestHub Healthcare, we believe that professional support should feel personal. Our diverse care options range from helping with daily routines to high-level clinical oversight, all delivered within the comfort of your own home.',
                                ],
                                [
                                    'title' => 'Helpful Hourly Homemaking',
                                    'description' => 'At WestHub Healthcare, our believe is deeply rooted in a tidy, peaceful home is the heart of well-being. Our dedicated homemakers in Barrington Township and Chicago provide a helping hand with the daily essentials; from light housekeeping and laundry to preparing your favorite nutritious meals.',
                                ],
                                [
                                    'title' => 'Home Health Aide Services',
                                    'description' => 'At WestHub Healthcare, we believe that every individual deserves to feel safe, dignified, and truly cared for in their own home. Our compassionate Home Health Aides are here to provide a helping hand with the personal tasks of daily life in Barrington Township.',
                                ],
                                [
                                    'title' => 'A Gentle Transition Home',
                                    'description' => 'Returning home after a hospital stay should be a time of peace and recovery. At WestHub Healthcare, our Barrington Township team is here to walk beside you, providing the heartfelt support needed for a safe and comfortable transition back to your own surroundings.',
                                ],
                            ],
                        ],
                        'footer_cta' => [
                            'title' => 'Get in touch for the Best Senior Care Services in Barrington Township.',
                            'description' => 'Explore how our Home Health Care Services in Barrington Township can enhance your loved one\'s quality of life. Contact our Barrington Township Caregivers today for personalized care solutions tailored to your needs.',
                        ],
                    ],
                ],
            );
        } catch (Throwable) {
            //
        }
    }
}
