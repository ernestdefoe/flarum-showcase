import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';
import type Tag from 'flarum/tags/common/models/Tag';

import { qualifyingPrimaryTagIds, qualifyingSecondaryTagIds } from './util';

interface LoaderState {
  loading: boolean;
  loaded: boolean;
  discussions: Discussion[];
}

const state: LoaderState = {
  loading: false,
  loaded: false,
  discussions: [],
};

export function showcaseState(): LoaderState {
  return state;
}

/**
 * Strategy: there are usually few secondary tags (Extensions / Themes) but
 * many primaries (one per extension). We fetch discussions carrying any
 * *secondary* tag in one request (comma = OR), ordered by the admin's sort
 * (the forum carries it as the API sort), then AND-filter the result against
 * the configured primary-tag-ID set client-side, keeping the server's order.
 */
export async function loadShowcase(): Promise<void> {
  if (state.loading || state.loaded) return;

  const primaryIds = qualifyingPrimaryTagIds();
  const secondaryIds = qualifyingSecondaryTagIds();
  if (primaryIds.length === 0 || secondaryIds.length === 0) {
    state.loaded = true;
    return;
  }

  state.loading = true;
  const max = Number(app.forum.attribute('showcaseMaxCards') ?? 12);

  try {
    const allTags = (await app.store.find<Tag[]>('tags')) ?? [];
    const secondarySlugs = allTags.filter((t) => secondaryIds.includes(Number(t.id()))).map((t) => String(t.slug()));

    if (secondarySlugs.length === 0) {
      state.loaded = true;
      return;
    }

    const list = await app.store.find<Discussion[]>('discussions', {
      filter: { tag: secondarySlugs.join(',') },
      include: 'user,tags,firstPost',
      page: { limit: max },
      sort: String(app.forum.attribute('showcaseSort') || '-lastPostedAt'),
    });

    const primarySet = new Set(primaryIds);
    const merged = list.filter((d) => {
      const tags = ((d as unknown as { tags?: () => Tag[] }).tags?.() ?? []) as Tag[];
      return tags.some((t) => t.position() !== null && primarySet.has(Number(t.id())));
    });

    state.discussions = merged;
    state.loaded = true;
  } finally {
    state.loading = false;
    m.redraw();
  }
}
