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
 * many primaries (one per extension). We fire one fetch per *secondary*
 * tag slug, then AND-filter the merged result against the configured
 * primary-tag-ID set client-side.
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
    const secondarySlugs = allTags
      .filter((t) => secondaryIds.includes(Number(t.id())))
      .map((t) => String(t.slug()));

    if (secondarySlugs.length === 0) {
      state.loaded = true;
      return;
    }

    const lists = await Promise.all(
      secondarySlugs.map((slug) =>
        app.store.find<Discussion[]>('discussions', {
          filter: { tag: slug },
          include: 'user,tags,firstPost',
          page: { limit: max },
          sort: '-lastPostedAt',
        })
      )
    );

    const primarySet = new Set(primaryIds);
    const seen = new Set<string>();
    const merged: Discussion[] = [];

    for (const list of lists) {
      for (const d of list) {
        const id = d.id();
        if (!id || seen.has(id)) continue;
        const tags = ((d as unknown as { tags?: () => Tag[] }).tags?.() ?? []) as Tag[];
        const matchesPrimary = tags.some(
          (t) => t.position() !== null && primarySet.has(Number(t.id()))
        );
        if (!matchesPrimary) continue;
        seen.add(id);
        merged.push(d);
        if (merged.length >= max) break;
      }
      if (merged.length >= max) break;
    }

    state.discussions = merged;
    state.loaded = true;
  } finally {
    state.loading = false;
    m.redraw();
  }
}
