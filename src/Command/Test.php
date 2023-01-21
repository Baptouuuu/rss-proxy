<?php
declare(strict_types = 1);

namespace RssProxy\Command;

use RssProxy\{
    Fetch,
    Filter,
};
use Innmind\Filesystem\Chunk;
use Innmind\Xml\{
    Reader,
    Node\Document,
};
use Innmind\CLI\{
    Console,
    Command,
};
use Innmind\Immutable\{
    Str,
    Predicate\Instance,
};

final class Test implements Command
{
    private Fetch $fetch;
    private Reader $read;
    private Filter $filter;

    public function __construct(Fetch $fetch, Reader $read, Filter $filter)
    {
        $this->fetch = $fetch;
        $this->read = $read;
        $this->filter = $filter;
    }

    public function __invoke(Console $console): Console
    {
        return ($this->fetch)()
            ->map(static fn($success) => $success->response()->body())
            ->flatMap(fn($content) => ($this->read)($content))
            ->keep(Instance::of(Document::class))
            ->map($this->filter)
            ->map(static fn($document) => $document->asContent())
            ->map(new Chunk)
            ->match(
                static fn($chunks) => $chunks->reduce(
                    $console,
                    static fn(Console $console, $chunk) => $console->output($chunk),
                ),
                static fn() => $console->error(Str::of("Unable to fetch the rss feed\n")),
            );
    }

    /**
     * @psalm-mutation-free
     */
    public function usage(): string
    {
        return <<<USAGE
        test

        Display the rss feed once the filter has been applied
        USAGE;
    }
}
