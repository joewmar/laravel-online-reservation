<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Archive>
 */
class ArchiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arrNationality = array(
            "Afghan",
            "Albanian",
            "Algerian",
            "American",
            "Andorran",
            "Angolan",
            "Antiguans",
            "Argentinean",
            "Armenian",
            "Australian",
            "Austrian",
            "Azerbaijani",
            "Bahamian",
            "Bahraini",
            "Bangladeshi",
            "Barbadian",
            "Barbudans",
            "Belarusian",
            "Belgian",
            "Belizean",
            "Beninese",
            "Bhutanese",
            "Bolivian",
            "Bosnian",
            "Brazilian",
            "British",
            "Bruneian",
            "Bulgarian",
            "Burkinabe",
            "Burmese",
            "Burundian",
            "Cambodian",
            "Cameroonian",
            "Canadian",
            "Cape Verdean",
            "Central African",
            "Chadian",
            "Chilean",
            "Chinese",
            "Colombian",
            "Comoran",
            "Congolese",
            "Costa Rican",
            "Croatian",
            "Cuban",
            "Cypriot",
            "Czech",
            "Danish",
            "Djibouti",
            "Dominican",
            "Dutch",
            "East Timorese",
            "Ecuadorean",
            "Egyptian",
            "Emirian",
            "Equatorial Guinean",
            "Eritrean",
            "Estonian",
            "Ethiopian",
            "Fijian",
            "Filipino",
            "Finnish",
            "French",
            "Gabonese",
            "Gambian",
            "Georgian",
            "German",
            "Ghanaian",
            "Greek",
            "Grenadian",
            "Guatemalan",
            "Guinea-Bissauan",
            "Guinean",
            "Guyanese",
            "Haitian",
            "Herzegovinian",
            "Honduran",
            "Hungarian",
            "I-Kiribati",
            "Icelander",
            "Indian",
            "Indonesian",
            "Iranian",
            "Iraqi",
            "Irish",
            "Israeli",
            "Italian",
            "Ivorian",
            "Jamaican",
            "Japanese",
            "Jordanian",
            "Kazakhstani",
            "Kenyan",
            "Kittian and Nevisian",
            "Kuwaiti",
            "Kyrgyz",
            "Laotian",
            "Latvian",
            "Lebanese",
            "Liberian",
            "Libyan",
            "Liechtensteiner",
            "Lithuanian",
            "Luxembourger",
            "Macedonian",
            "Malagasy",
            "Malawian",
            "Malaysian",
            "Maldivan",
            "Malian",
            "Maltese",
            "Marshallese",
            "Mauritanian",
            "Mauritian",
            "Mexican",
            "Micronesian",
            "Moldovan",
            "Monacan",
            "Mongolian",
            "Moroccan",
            "Mosotho",
            "Motswana",
            "Mozambican",
            "Namibian",
            "Nauruan",
            "Nepalese",
            "New Zealander",
            "Nicaraguan",
            "Nigerian",
            "Nigerien",
            "North Korean",
            "Northern Irish",
            "Norwegian",
            "Omani",
            "Pakistani",
            "Palauan",
            "Panamanian",
            "Papua New Guinean",
            "Paraguayan",
            "Peruvian",
            "Polish",
            "Portuguese",
            "Qatari",
            "Romanian",
            "Russian",
            );
            $arrAmount = [];
            $arrMenu = [];
            $arrAddMenu = [];
            $arrRooms = [];
        for($count = 0; $count < fake()->numberBetween(1, 10); $count++){
            $arrAmount[] = (string)fake()->randomElement(['tm', 'rid',]) . (string)fake()->numberBetween(1, 15)  . '-' . (string)fake()->randomFloat(2, 1100, 20000);
        }
        for($count = 0; $count <  fake()->numberBetween(1, 10); $count++){
            $arrMenu[] = (string)fake()->randomDigit();
        }
        for($count = 0; $count <  fake()->numberBetween(1, 10); $count++){
            $arrAddMenu[] = (string) fake()->randomDigit();
        }
        for($count = 0; $count < fake()->numberBetween(1, 15); $count++){
            $arrRooms[] = (string)  fake()->randomDigit();
        }
        return [
        //    'name' => fake()->firstName() . ' ' . fake()->lastName(),
        //    'age' => Carbon::createFromFormat('Y-m-d', fake()->date('Y-m-d'))->age,
        //    'country' => fake()->country(),
        //    'nationality' => fake()->randomElement($arrNationality) ,
        //    'contact' => fake()->e164PhoneNumber(),
        //    'email' => fake()->unique()->freeEmail(),
        //    'pax' => fake()->numberBetween(1, 12),
        //    'room_id' => implode(',', $arrRooms),
        //    'accommodation_type' => fake()->randomElement(['Room Only', 'Day Tour', 'Overnight']),
        //    'payment_method' => fake()->randomElement(['Walk-in', 'Gcash', 'PayPal']),
        //    'menu' => implode(',', $arrMenu),
           'check_in' => Carbon::now()->addRealDays(fake()->numberBetween(5, 100))->format('Y-m-d'),
        //    'check_out' =>  fake()->date(),
           'status' => fake()->numberBetween(0, 2), /* 0 => done, 1 => disaprove, 2 => cancellation, 3 => ? */
        // //    'additional_menu' => implode(',', $arrAddMenu),
        //    'amount' => implode(',', $arrAmount),
           'total' => fake()->randomFloat(2, 1100, 50000),
        //    'message' => fake()->sentences(2) ,
        ];
    }
}
