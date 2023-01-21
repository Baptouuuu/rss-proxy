<?php
declare(strict_types = 1);

namespace RssProxy;

use Innmind\Xml\{
    Node,
    Node\Document,
    Element,
};
use Innmind\Immutable\{
    Str,
    Maybe,
    Predicate\Instance,
};

/**
 * @psalm-immutable
 */
final class Filter
{
    public function __invoke(Document $document): Document
    {
        return $document->mapChild(
            fn($node) => Maybe::just($node)
                ->keep(Instance::of(Element::class))
                ->filter(static fn($element) => $element->name() === 'feed')
                ->match(
                    fn($feed) => $this->filter($feed),
                    static fn() => $node,
                ),
        );
    }

    private function filter(Element $feed): Node
    {
        return $feed->filterChild(
            fn($node) => Maybe::just($node)
                ->keep(Instance::of(Element::class))
                ->filter(static fn($element) => $element->name() === 'entry')
                ->match(
                    fn($entry) => $this->acceptsEntry($entry),
                    static fn() => true, // accept any other child of the document
                ),
        );
    }

    private function acceptsEntry(Element $entry): bool
    {
        return $entry
            ->children()
            ->keep(Instance::of(Element::class))
            ->find(static fn($element) => $element->name() === 'link')
            ->flatMap(static fn($link) => $link->attributes()->get('href'))
            ->map(static fn($href) => $href->value())
            ->map(Str::of(...))
            ->filter(static fn($href) => $href->startsWith('https://www.theatlantic.com/science'))
            ->match(
                static fn() => true,
                static fn() => false,
            );
    }
}
