<?php

namespace Ernestdefoe\Showcase\Showcase;

use Flarum\Extension\ExtensionManager;

/**
 * Turns the "Sort order" setting into the `sort` the forum asks the
 * discussions API for, so the server returns the right top N cards.
 */
class ApiSort
{
    public function __construct(private readonly ExtensionManager $extensions)
    {
    }

    public function __invoke(mixed $value): string
    {
        return match ($value) {
            'most_replied' => '-commentCount,-lastPostedAt',
            // Only flarum/sticky has the column; without it nothing is pinned.
            'pinned_first' => $this->extensions->isEnabled('flarum-sticky') ? '-isSticky,-lastPostedAt' : '-lastPostedAt',
            default => '-lastPostedAt',
        };
    }
}
