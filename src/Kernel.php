<?php
declare(strict_types = 1);

namespace RssProxy;

use RssProxy\Command\Test;
use Innmind\Framework\{
    Application,
    Middleware,
};
use Innmind\Xml\Reader\Reader;

final class Kernel implements Middleware
{
    public function __invoke(Application $app): Application
    {
        return $app
            ->command(static fn($_, $os) => new Test(
                $os,
                Reader::of(),
                new Filter,
            ));
    }
}
