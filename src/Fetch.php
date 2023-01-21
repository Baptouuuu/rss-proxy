<?php
declare(strict_types = 1);

namespace RssProxy;

use Innmind\OperatingSystem\OperatingSystem;
use Innmind\HttpTransport\Success;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method,
    ProtocolVersion,
};
use Innmind\Url\Url;
use Innmind\Immutable\Maybe;

final class Fetch
{
    private OperatingSystem $os;

    public function __construct(OperatingSystem $os)
    {
        $this->os = $os;
    }

    /**
     * @return Maybe<Success>
     */
    public function __invoke(): Maybe
    {
        return $this
            ->os
            ->remote()
            ->http()(new Request(
                Url::of('https://www.theatlantic.com/feed/all/'),
                Method::get,
                ProtocolVersion::v20,
            ))
            ->maybe();
    }
}
