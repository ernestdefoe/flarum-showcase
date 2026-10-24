<?php

namespace Ernestdefoe\Showcase\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The forum asks the discussions API for the showcase with the `sort` it
 * carries as `showcaseSort`; every "Sort order" option must change the order.
 */
class SortOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'ernestdefoe-showcase');

        $this->prepareDatabase([
            Tag::class => [
                ['id' => 2, 'name' => 'Released', 'slug' => 'released', 'position' => null, 'is_primary' => false],
                ['id' => 3, 'name' => 'Themes', 'slug' => 'themes', 'position' => null, 'is_primary' => false],
            ],
            Discussion::class => [
                // Newest activity, fewest replies.
                ['id' => 1, 'title' => 'Fresh', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'comment_count' => 1],
                // Most replies, older activity, and on the second secondary tag.
                ['id' => 2, 'title' => 'Busy', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now()->subDays(2), 'user_id' => 1, 'comment_count' => 30],
                // Oldest activity: the one an admin pins.
                ['id' => 3, 'title' => 'Pinned', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now()->subDays(5), 'user_id' => 1, 'comment_count' => 2],
            ],
            'discussion_tag' => [
                ['discussion_id' => 1, 'tag_id' => 2],
                ['discussion_id' => 2, 'tag_id' => 3],
                ['discussion_id' => 3, 'tag_id' => 2],
            ],
        ]);
    }

    /** The titles in the order the forum's showcase request returns them. */
    private function showcaseOrder(): array
    {
        $forum = json_decode((string) $this->send($this->request('GET', '/api'))->getBody(), true);
        $sort = $forum['data']['attributes']['showcaseSort'];

        $response = $this->send($this->request('GET', '/api/discussions')->withQueryParams([
            'filter' => ['tag' => 'released,themes'],
            'sort' => $sort,
        ]));

        $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());

        return array_map(fn ($d) => $d['attributes']['title'], json_decode((string) $response->getBody(), true)['data']);
    }

    /** Enable flarum/sticky and pin "Pinned". Settings must be set before this boots the app. */
    private function pin(): void
    {
        $this->extension('flarum-tags', 'flarum-sticky', 'ernestdefoe-showcase');
        $this->app();
        Discussion::query()->whereKey(3)->update(['is_sticky' => true]);
    }

    #[Test]
    public function latest_activity_comes_first_by_default()
    {
        $this->assertSame(['Fresh', 'Busy', 'Pinned'], $this->showcaseOrder());
    }

    #[Test]
    public function latest_activity_option()
    {
        $this->setting('ernestdefoe-showcase.sort', 'latest');

        $this->assertSame(['Fresh', 'Busy', 'Pinned'], $this->showcaseOrder());
    }

    #[Test]
    public function most_replied_option()
    {
        $this->setting('ernestdefoe-showcase.sort', 'most_replied');

        $this->assertSame(['Busy', 'Pinned', 'Fresh'], $this->showcaseOrder());
    }

    #[Test]
    public function pinned_first_option()
    {
        $this->setting('ernestdefoe-showcase.sort', 'pinned_first');
        $this->pin();

        $this->assertSame(['Pinned', 'Fresh', 'Busy'], $this->showcaseOrder());
    }

    #[Test]
    public function pinned_first_without_sticky_falls_back_to_latest()
    {
        $this->setting('ernestdefoe-showcase.sort', 'pinned_first');

        $this->assertSame(['Fresh', 'Busy', 'Pinned'], $this->showcaseOrder());
    }

    #[Test]
    public function latest_ignores_pins()
    {
        $this->setting('ernestdefoe-showcase.sort', 'latest');
        $this->pin();

        $this->assertSame(['Fresh', 'Busy', 'Pinned'], $this->showcaseOrder());
    }
}
