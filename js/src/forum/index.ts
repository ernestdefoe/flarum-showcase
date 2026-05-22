import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import ItemList from 'flarum/common/utils/ItemList';
import type Mithril from 'mithril';

import ShowcaseSection from './components/ShowcaseSection';
import { loadShowcase, showcaseState } from './loader';

app.initializers.add('ernestdefoe-showcase', () => {
  extend(IndexPage.prototype, 'oninit', function () {
    // Fire and forget — Promise resolution triggers m.redraw().
    loadShowcase();
  });

  extend(IndexPage.prototype, 'contentItems', function (items: ItemList<Mithril.Children>) {
    const { discussions, loading } = showcaseState();
    if (loading || discussions.length === 0) return;
    items.add('showcase', m(ShowcaseSection, { discussions }), 95);
  });
});
