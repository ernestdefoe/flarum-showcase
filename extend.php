<?php

use Flarum\Extend;
use Flarum\Api\Resource\DiscussionResource;
use Ernestdefoe\Showcase\Api\AddCoverImageField;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js')
        ->css(__DIR__ . '/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/locale'),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(AddCoverImageField::class),

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
        ->serializeToForum('showcaseSort', 'ernestdefoe-showcase.sort'),
];
