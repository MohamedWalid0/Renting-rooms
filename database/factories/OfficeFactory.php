<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfficeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            
            'user_id' => User::factory() ,
            'title' => $this->faker->sentence ,
            'description' => $this->faker->paragraph ,
            'lat' => $this->faker->latitude ,
            'lng' => $this->faker->longitude ,
            'address_line1' => $this->faker->address ,
            'approval_status' =>Office::APPROVAL_APPROVED ,
            'hidden' => false ,
            'price_per_day' => $this->faker->numberBetween(100,2000) ,
            'monthly_discount' => 0 ,

        ];
    }


    public function pending()
    {
        return $this->state([
            'approval_status' => Office::APPROVAL_PENDING
        ]);
    }


    
    public function hidden()
    {
        return $this->state([
            'hidden' => true
        ]);
    }


}
