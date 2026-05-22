<?php

namespace Ernestdefoe\Showcase\Showcase;

use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;

class Qualifier
{
    public function __construct(private readonly SettingsRepositoryInterface $settings)
    {
    }

    /** @return int[] */
    public function primaryTagIds(): array
    {
        return $this->decode('ernestdefoe-showcase.primary_tag_ids');
    }

    /** @return int[] */
    public function secondaryTagIds(): array
    {
        return $this->decode('ernestdefoe-showcase.secondary_tag_ids');
    }

    /**
     * AND semantics: discussion qualifies iff it carries at least one primary
     * tag whose id is whitelisted AND at least one secondary tag whose id is
     * whitelisted. An empty whitelist on either side disqualifies everything
     * (admin must explicitly opt in to populate the showcase).
     */
    public function qualifies(Discussion $discussion): bool
    {
        $primary = $this->primaryTagIds();
        $secondary = $this->secondaryTagIds();

        if ($primary === [] || $secondary === []) {
            return false;
        }

        $hasPrimary = false;
        $hasSecondary = false;

        foreach ($discussion->tags as $tag) {
            $id = (int) $tag->id;
            if ($tag->is_primary) {
                if (in_array($id, $primary, true)) {
                    $hasPrimary = true;
                }
            } else {
                if (in_array($id, $secondary, true)) {
                    $hasSecondary = true;
                }
            }
            if ($hasPrimary && $hasSecondary) {
                return true;
            }
        }

        return false;
    }

    /** @return int[] */
    private function decode(string $key): array
    {
        $raw = (string) ($this->settings->get($key) ?? '[]');
        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }
}
