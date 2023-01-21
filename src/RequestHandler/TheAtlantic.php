<?php
declare(strict_types = 1);

namespace RssProxy\RequestHandler;

use RssProxy\{
    Fetch,
    Filter,
};
use Innmind\Framework\Http\RequestHandler;
use Innmind\Xml\{
    Reader,
    Node\Document,
};
use Innmind\Http\{
    Message\ServerRequest,
    Message\Response,
    Message\StatusCode,
    Headers,
    Header\ContentType,
    Header\Parameter\Parameter,
};
use Innmind\Immutable\Predicate\Instance;

final class TheAtlantic implements RequestHandler
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

    public function __invoke(ServerRequest $request): Response
    {
        return ($this->fetch)()
            ->map(static fn($success) => $success->response())
            ->flatMap(
                fn($response) => ($this->read)($response->body())
                    ->keep(Instance::of(Document::class))
                    ->map($this->filter)
                    ->map(static fn($document) => $document->asContent())
                    ->map(static fn($content) => new Response\Response(
                        $response->statusCode(),
                        $response->protocolVersion(),
                        Headers::of(
                            ContentType::of('application', 'xml', new Parameter('charset', 'utf-8')),
                        ),
                        $content,
                    )),
            )
            ->match(
                static fn($response) => $response,
                static fn() => new Response\Response(
                    StatusCode::notFound,
                    $request->protocolVersion(),
                ),
            );
    }
}
