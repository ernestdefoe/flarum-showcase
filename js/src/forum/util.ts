import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';

export function qualifyingPrimaryTagIds(): number[] {
  return readIdList('showcasePrimaryTagIds');
}

export function qualifyingSecondaryTagIds(): number[] {
  return readIdList('showcaseSecondaryTagIds');
}

function readIdList(attr: string): number[] {
  const raw = app.forum.attribute(attr);
  if (!Array.isArray(raw)) return [];
  return raw.map((n) => Number(n)).filter((n) => Number.isFinite(n));
}

/**
 * Tag accessor — Discussion exposes `tags()` at runtime via the tags
 * extension. Types don't know about it without flarum/tags' augmentation,
 * so we cast through `unknown` to keep tsc honest.
 */
function tagsOf(d: Discussion): Tag[] {
  return ((d as unknown as { tags?: () => Tag[] | undefined }).tags?.() ?? []) as Tag[];
}

export function isShowcaseDiscussion(discussion: Discussion): boolean {
  const primary = qualifyingPrimaryTagIds();
  const secondary = qualifyingSecondaryTagIds();
  if (primary.length === 0 || secondary.length === 0) return false;

  let hasPrimary = false;
  let hasSecondary = false;
  for (const tag of tagsOf(discussion)) {
    const id = Number(tag.id());
    if (tag.position() !== null) {
      if (primary.includes(id)) hasPrimary = true;
    } else {
      if (secondary.includes(id)) hasSecondary = true;
    }
    if (hasPrimary && hasSecondary) return true;
  }
  return false;
}

/** Used by ShowcaseCard to render the two chip labels. */
export function splitTags(discussion: Discussion): { primary: Tag | null; secondary: Tag | null } {
  const primaryIds = qualifyingPrimaryTagIds();
  const secondaryIds = qualifyingSecondaryTagIds();
  let primary: Tag | null = null;
  let secondary: Tag | null = null;
  for (const tag of tagsOf(discussion)) {
    const id = Number(tag.id());
    if (tag.position() !== null && primary == null && primaryIds.includes(id)) {
      primary = tag;
    } else if (tag.position() === null && secondary == null && secondaryIds.includes(id)) {
      secondary = tag;
    }
  }
  return { primary, secondary };
}
