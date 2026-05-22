<?php

namespace Ernestdefoe\Showcase\Api;

use Ernestdefoe\Showcase\CoverImage\Resolver;
use Ernestdefoe\Showcase\Showcase\Qualifier;
use Flarum\Discussion\Discussion;
use Flarum\Api\Schema;

class AddCoverImageField
{
    public function __construct(
        private readonly Resolver $resolver,
        private readonly Qualifier $qualifier,
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('showcaseCoverUrl')
                ->get(function (Discussion $discussion): ?string {
                    if (! $this->qualifier->qualifies($discussion)) {
                        return null;
                    }

                    return $this->resolver->resolve($discussion);
                }),
        ];
    }
}
