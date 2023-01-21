<?php

declare(strict_types = 1);

require __DIR__.'/../vendor/autoload.php';

use RssProxy\Kernel;
use Innmind\Framework\{
    Main\Http,
    Application,
};

new class extends Http {
    protected function configure(Application $app): Application
    {
        return $app->map(new Kernel);
    }
};
