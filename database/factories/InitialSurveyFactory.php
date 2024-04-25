<?php

namespace Database\Factories;

use App\Models\InitialSurvey;
use Illuminate\Database\Eloquent\Factories\Factory;

class InitialSurveyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InitialSurvey::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'question' => $this->faker->sentence,
            'image' => $this->faker->imageUrl(),
            'answer' => $this->faker->word,
            'points' => $this->faker->randomElement([10, 30, 50]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
