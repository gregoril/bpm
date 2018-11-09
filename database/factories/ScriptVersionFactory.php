<?php

use Faker\Generator as Faker;
use ProcessMaker\Models\Script;
use ProcessMaker\Models\ScriptVersion;

$factory->define(ScriptVersion::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'language' => $faker->randomElement(['php', 'lua']),
        'code' => $faker->sentence($faker->randomDigitNotNull),
        'description' => $faker->sentence,
        'script_id' => function () {
            return factory(Script::class)->create()->getKey();
        }
    ];
});
