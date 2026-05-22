<?php

namespace Ernestdefoe\Showcase\CoverImage;

use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;

class Resolver
{
    /**
     * Permissive shortcode pattern. Accepts single or double quotes and
     * forgives extra whitespace around `=`. Captures owner ($1) and repo ($2).
     */
    private const SHORTCODE_PATTERN =
        '/\[gh-readme\s+repo\s*=\s*["\']([^"\'\/\s]+)\/([^"\'\s\]]+)["\']\s*\]/i';

    public function resolve(Discussion $discussion): ?string
    {
        $firstPost = $discussion->firstPost;

        if (! $firstPost instanceof CommentPost) {
            return null;
        }

        $content = (string) $firstPost->content;

        if ($content === '' || ! preg_match(self::SHORTCODE_PATTERN, $content, $m)) {
            return null;
        }

        return $this->openGraphUrl($m[1], $m[2]);
    }

    /**
     * GitHub's social-preview endpoint. The first path segment is a
     * cache-busting token — any non-empty value works. Repos without a
     * custom social preview still return an auto-generated card here, so
     * we don't need to probe whether one exists.
     */
    private function openGraphUrl(string $owner, string $repo): string
    {
        return sprintf(
            'https://opengraph.githubassets.com/1/%s/%s',
            rawurlencode($owner),
            rawurlencode($repo)
        );
    }
}
