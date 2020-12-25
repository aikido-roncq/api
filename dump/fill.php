<?php

use App\Factory;
use Cocur\Slugify\Slugify;
use Faker\Factory as FakerFactory;
use Dotenv\Dotenv;
use Ludal\QueryBuilder\QueryBuilder;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable('.')->load();

$faker = FakerFactory::create();
$builder = new QueryBuilder(Factory::pdo());
$slugify = new Slugify();

$builder->deleteFrom('articles')->execute();
$builder->deleteFrom('events')->execute();
$builder->deleteFrom('gallery')->execute();

// INSERT ARTICLES
for ($i = 0; $i < 10; $i++) {
    $title = $faker->words(4, true);
    $slug = $slugify->slugify($title);
    $content = "<p>{$faker->paragraphs(3, true)}</p>";
    $date = $faker->dateTimeBetween('-5 months', 'now')->format('Y-m-d H:i:s');

    $builder
        ->insertInto('articles')
        ->values(compact('title', 'content', 'slug', 'date'))
        ->execute();
}

// INSERT EVENTS
for ($i = 0; $i < 10; $i++) {
    $title = $faker->words(4, true);
    $info = mb_substr($faker->paragraphs(1, true), 0, 250);
    $start_date = $faker->dateTimeBetween('-10 days', '+1 month')->format('Y-m-d');
    $end_date = $faker->dateTimeBetween($start_date, "$start_date +1 month")->format('Y-m-d');

    // to get one-day events
    if ($i < 3)
        $end_date = $start_date;

    $builder
        ->insertInto('events')
        ->values(compact('start_date', 'end_date', 'title', 'info'))
        ->execute();
}

// INSERT IMAGES
for ($i = 0; $i < 10; $i++) {
    $src = sprintf('assets/gallery/%s.jpg', $faker->word());
    $caption = $i < 4 ? null : $faker->sentence();
    $added = $faker->dateTimeBetween('-15 days', 'today')->format('Y-m-d');

    $builder
        ->insertInto('gallery')
        ->values(compact('src', 'caption', 'added'))
        ->execute();
}
