<?php
declare(strict_types = 1);

namespace RssProxy;

use RssProxy\{
    Command\Test,
    RequestHandler\TheAtlantic,
};
use Innmind\Framework\{
    Application,
    Middleware,
    Http\Service,
};
use Innmind\Xml\Reader\Reader;
use Innmind\Router\Route;

/**
 * @psalm-suppress ArgumentTypeCoercion
 */
final class Kernel implements Middleware
{
    public function __invoke(Application $app): Application
    {
        return $app
            ->service('reader', static fn() => Reader::of())
            ->service('filter', static fn() => new Filter)
            ->service('fetch', static fn($_, $os) => new Fetch($os))
            ->service('the-atlantic', static fn($get) => new TheAtlantic(
                $get('fetch'),
                $get('reader'),
                $get('filter'),
            ))
            ->command(static fn($get) => new Test(
                $get('fetch'),
                $get('reader'),
                $get('filter'),
            ))
            ->appendRoutes(static fn($routes, $container) => $routes->add(
                Route::literal('GET /the-atlantic/science')->handle(
                    Service::of($container, 'the-atlantic'),
                ),
            ));
    }
}
