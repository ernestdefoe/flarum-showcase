<?php

namespace Ernestdefoe\Showcase\Api;

use Ernestdefoe\Showcase\CoverImage\Resolver;
use Ernestdefoe\Showcase\Showcase\Qualifier;
use Flarum\Discussion\Discussion;
use Flarum\Api\Schema;
use Illuminate\Database\Eloquent\Collection;

class AddCoverImageField
{
    /**
     * Qualifying discussions whose first post is not loaded yet.
     *
     * The cover is read from the first post, which an ordinary discussion
     * list does not load. Reading `$discussion->firstPost` in the getter was
     * one query per qualifying discussion on the page. Instead the getter
     * notes the discussion and returns a closure, which the serializer
     * resolves after visiting the whole page; the first one to run loads every
     * noted first post in one query.
     *
     * @var Discussion[]
     */
    private array $pending = [];

    public function __construct(
        private readonly Resolver $resolver,
        private readonly Qualifier $qualifier,
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('showcaseCoverUrl')
                ->get(function (Discussion $discussion) {
                    if (! $this->qualifier->qualifies($discussion)) {
                        return null;
                    }

                    if ($discussion->relationLoaded('firstPost')) {
                        return $this->resolver->resolve($discussion);
                    }

                    $this->pending[] = $discussion;

                    return function () use ($discussion): ?string {
                        if ($this->pending !== []) {
                            (new Collection($this->pending))->loadMissing('firstPost');
                            $this->pending = [];
                        }

                        return $this->resolver->resolve($discussion);
                    };
                }),
        ];
    }
}
