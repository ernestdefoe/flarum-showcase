<?php

use Ernestdefoe\Showcase\Api\AddCoverImageField;
use Ernestdefoe\Showcase\Showcase\ApiSort;
use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Sort\SortColumn;
use Flarum\Extend;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(AddCoverImageField::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-sticky', fn () => [
            // Discussion search orders by the snake_case of the sort's name, so the
            // name must be the column's: `isSticky` sorts by `is_sticky`.
            (new Extend\ApiResource(DiscussionResource::class))
                ->sorts(fn () => [SortColumn::make('isSticky')]),
        ]),

    (new Extend\Settings())
        ->default('ernestdefoe-showcase.display_style', 'carousel')
        ->default('ernestdefoe-showcase.primary_tag_ids', '[]')
        ->default('ernestdefoe-showcase.secondary_tag_ids', '[]')
        ->default('ernestdefoe-showcase.max_cards', 12)
        ->default('ernestdefoe-showcase.sort', 'latest')
        ->serializeToForum('showcaseDisplayStyle', 'ernestdefoe-showcase.display_style')
        ->serializeToForum('showcasePrimaryTagIds', 'ernestdefoe-showcase.primary_tag_ids', function ($v) {
            $decoded = json_decode((string) ($v ?? '[]'), true);

            return is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
        })
        ->serializeToForum('showcaseSecondaryTagIds', 'ernestdefoe-showcase.secondary_tag_ids', function ($v) {
            $decoded = json_decode((string) ($v ?? '[]'), true);

            return is_array($decoded) ? array_values(array_map('intval', $decoded)) : [];
        })
        ->serializeToForum('showcaseMaxCards', 'ernestdefoe-showcase.max_cards', fn ($v) => (int) $v)
        ->serializeToForum('showcaseSort', 'ernestdefoe-showcase.sort', ApiSort::class),
];
