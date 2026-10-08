<?php

namespace Ernestdefoe\Showcase\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Tags\Tag;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class ShowcaseTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-tags', 'ernestdefoe-showcase');

        $this->prepareDatabase([
            User::class => [$this->normalUser()],
            Tag::class => [
                ['id' => 2, 'name' => 'Extensions', 'slug' => 'extensions', 'position' => 1, 'is_primary' => true],
                ['id' => 3, 'name' => 'Released', 'slug' => 'released', 'position' => null, 'is_primary' => false],
                ['id' => 4, 'name' => 'Draft', 'slug' => 'draft', 'position' => null, 'is_primary' => false],
            ],
        ]);
        $this->showcase(1, 'Showcased', [2, 3], '[gh-readme repo="acme/rocket"]');
        $this->showcase(2, 'No README', [2, 3], 'Just words');
        $this->showcase(3, 'Not released', [2, 4], '[gh-readme repo="acme/draft"]');
        $this->showcase(4, 'Untagged', [], '[gh-readme repo="acme/stray"]');

        $this->setting('ernestdefoe-showcase.primary_tag_ids', '[2]');
        $this->setting('ernestdefoe-showcase.secondary_tag_ids', '["3"]');
    }

    private function showcase(int $id, string $title, array $tags, string $content): void
    {
        $this->prepareDatabase([
            Discussion::class => [['id' => $id, 'title' => $title, 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 2, 'first_post_id' => $id, 'comment_count' => 1]],
            Post::class => [['id' => $id, 'discussion_id' => $id, 'number' => 1, 'user_id' => 2, 'type' => 'comment', 'content' => '<t>'.htmlspecialchars($content, ENT_XML1).'</t>', 'created_at' => Carbon::now()]],
            'discussion_tag' => array_map(fn ($tag) => ['discussion_id' => $id, 'tag_id' => $tag], $tags),
        ]);
    }

    private function covers(): array
    {
        $response = $this->send($this->request('GET', '/api/discussions'));
        $data = json_decode((string) $response->getBody(), true)['data'];

        return array_column(array_map(fn ($d) => ['title' => $d['attributes']['title'], 'cover' => $d['attributes']['showcaseCoverUrl']], $data), 'cover', 'title');
    }

    #[Test]
    public function a_qualifying_discussion_gets_its_repositorys_cover()
    {
        $covers = $this->covers();

        $this->assertSame('https://opengraph.githubassets.com/1/acme/rocket', $covers['Showcased']);
        $this->assertNull($covers['No README'], 'No shortcode, no cover');
        $this->assertNull($covers['Not released'], 'Needs a whitelisted secondary tag too');
        $this->assertNull($covers['Untagged']);
    }

    #[Test]
    public function an_empty_whitelist_showcases_nothing()
    {
        $this->setting('ernestdefoe-showcase.secondary_tag_ids', '[]');

        $this->assertNull($this->covers()['Showcased']);
    }

    #[Test]
    public function covers_take_a_fixed_number_of_queries()
    {
        for ($id = 10; $id < 25; $id++) {
            $this->showcase($id, "Rocket $id", [2, 3], "[gh-readme repo=\"acme/rocket-$id\"]");
        }

        // The repeated-query detector fails the request on a per-discussion query.
        $covers = $this->covers();

        $this->assertSame('https://opengraph.githubassets.com/1/acme/rocket-12', $covers['Rocket 12']);
    }

    #[Test]
    public function the_forum_carries_the_settings_as_numbers()
    {
        // Stored as text, as the admin page saves them.
        $this->setting('ernestdefoe-showcase.primary_tag_ids', '["2"]');
        $this->setting('ernestdefoe-showcase.max_cards', '9');

        $attributes = json_decode((string) $this->send($this->request('GET', '/api'))->getBody(), true)['data']['attributes'];

        $this->assertSame([2], $attributes['showcasePrimaryTagIds']);
        $this->assertSame([3], $attributes['showcaseSecondaryTagIds']);
        $this->assertSame(9, $attributes['showcaseMaxCards']);
        $this->assertSame('carousel', $attributes['showcaseDisplayStyle']);
    }
}
