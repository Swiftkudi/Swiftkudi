<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $universities = [
            // Federal Universities
            ['name' => 'University of Ibadan', 'domain' => 'ui.edu.ng', 'city' => 'Ibadan', 'state' => 'Oyo', 'is_active' => true],
            ['name' => 'University of Lagos', 'domain' => 'unilag.edu.ng', 'city' => 'Lagos', 'state' => 'Lagos', 'is_active' => true],
            ['name' => 'Ahmadu Bello University', 'domain' => 'abu.edu.ng', 'city' => 'Zaria', 'state' => 'Kaduna', 'is_active' => true],
            ['name' => 'University of Nigeria', 'domain' => 'unn.edu.ng', 'city' => 'Nsukka', 'state' => 'Enugu', 'is_active' => true],
            ['name' => 'Obafemi Awolowo University', 'domain' => 'oauife.edu.ng', 'city' => 'Ile-Ife', 'state' => 'Osun', 'is_active' => true],
            ['name' => 'University of Benin', 'domain' => 'uniben.edu.ng', 'city' => 'Benin City', 'state' => 'Edo', 'is_active' => true],
            ['name' => 'Federal University of Technology Minna', 'domain' => 'futminna.edu.ng', 'city' => 'Minna', 'state' => 'Niger', 'is_active' => true],
            ['name' => 'Federal University of Technology Akure', 'domain' => 'futa.edu.ng', 'city' => 'Akure', 'state' => 'Ondo', 'is_active' => true],
            ['name' => 'Federal University of Technology Owerri', 'domain' => 'futo.edu.ng', 'city' => 'Owerri', 'state' => 'Imo', 'is_active' => true],
            ['name' => 'Bayero University Kano', 'domain' => 'buk.edu.ng', 'city' => 'Kano', 'state' => 'Kano', 'is_active' => true],
            
            // State Universities
            ['name' => 'Lagos State University', 'domain' => 'lasu.edu.ng', 'city' => 'Lagos', 'state' => 'Lagos', 'is_active' => true],
            ['name' => 'Covenant University', 'domain' => 'covenantuniversity.edu.ng', 'city' => 'Ota', 'state' => 'Ogun', 'is_active' => true],
            ['name' => 'University of Ilorin', 'domain' => 'unilorin.edu.ng', 'city' => 'Ilorin', 'state' => 'Kwara', 'is_active' => true],
            ['name' => 'Nnamdi Azikiwe University', 'domain' => 'unizik.edu.ng', 'city' => 'Awka', 'state' => 'Anambra', 'is_active' => true],
            ['name' => 'Enugu State University of Science and Technology', 'domain' => 'esut.edu.ng', 'city' => 'Enugu', 'state' => 'Enugu', 'is_active' => true],
            ['name' => 'Osun State University', 'domain' => 'uniosun.edu.ng', 'city' => 'Osogbo', 'state' => 'Osun', 'is_active' => true],
            ['name' => 'Ekiti State University', 'domain' => 'eksu.edu.ng', 'city' => 'Ado-Ekiti', 'state' => 'Ekiti', 'is_active' => true],
            ['name' => 'Abubakar Tafawa Balewa University', 'domain' => 'atbu.edu.ng', 'city' => 'Bauchi', 'state' => 'Bauchi', 'is_active' => true],
            ['name' => 'Kano University of Science and Technology', 'domain' => 'kust.edu.ng', 'city' => 'Kano', 'state' => 'Kano', 'is_active' => true],
            ['name' => 'Kaduna State University', 'domain' => 'kasu.edu.ng', 'city' => 'Kaduna', 'state' => 'Kaduna', 'is_active' => true],
            
            // Private Universities
            ['name' => 'University of Port Harcourt', 'domain' => 'uniport.edu.ng', 'city' => 'Port Harcourt', 'state' => 'Rivers', 'is_active' => true],
            ['name' => 'Pan-Atlantic University', 'domain' => 'pau.edu.ng', 'city' => 'Lagos', 'state' => 'Lagos', 'is_active' => true],
            ['name' => 'American University of Nigeria', 'domain' => 'aun.edu.ng', 'city' => 'Yola', 'state' => 'Adamawa', 'is_active' => true],
            ['name' => 'Redeemer\'s University', 'domain' => 'run.edu.ng', 'city' => 'Ede', 'state' => 'Osun', 'is_active' => true],
            ['name' => 'Bowen University', 'domain' => 'bowenuniversity.edu.ng', 'city' => 'Iwo', 'state' => 'Osun', 'is_active' => true],
            ['name' => 'University of Calabar', 'domain' => 'unical.edu.ng', 'city' => 'Calabar', 'state' => 'Cross River', 'is_active' => true],
            ['name' => 'Niger Delta University', 'domain' => 'ndu.edu.ng', 'city' => 'Wilberforce Island', 'state' => 'Bayelsa', 'is_active' => true],
            ['name' => 'Federal University Lafia', 'domain' => 'fulafia.edu.ng', 'city' => 'Lafia', 'state' => 'Nasarawa', 'is_active' => true],
            ['name' => 'University of Jos', 'domain' => 'unijos.edu.ng', 'city' => 'Jos', 'state' => 'Plateau', 'is_active' => true],
            ['name' => 'Taraba State University', 'domain' => 'tarsu.edu.ng', 'city' => 'Jalingo', 'state' => 'Taraba', 'is_active' => true],
        ];

        foreach ($universities as $university) {
            University::firstOrCreate(
                ['domain' => $university['domain']],
                [
                    'name' => $university['name'],
                    'slug' => Str::slug($university['name']),
                    'city' => $university['city'],
                    'state' => $university['state'],
                    'is_active' => $university['is_active'],
                ]
            );
        }
    }
}
