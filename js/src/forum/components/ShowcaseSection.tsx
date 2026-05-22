import Component from 'flarum/common/Component';
import app from 'flarum/forum/app';
import type Discussion from 'flarum/common/models/Discussion';

import ShowcaseCarousel from './ShowcaseCarousel';
import ShowcaseGrid from './ShowcaseGrid';

interface Attrs {
  discussions: Discussion[];
}

export default class ShowcaseSection extends Component<Attrs> {
  view() {
    const { discussions } = this.attrs;
    if (!discussions || discussions.length === 0) return null;

    const style = String(app.forum.attribute('showcaseDisplayStyle') ?? 'carousel');
    const tr = (key: string) => app.translator.trans(`ernestdefoe-showcase.forum.section.${key}`);

    return (
      <section className="Showcase">
        <header className="Showcase-header">
          <h2 className="Showcase-title">
            <span className="Showcase-title-star" aria-hidden="true">
              ★
            </span>{' '}
            {tr('title')}
          </h2>
          <p className="Showcase-subtitle">{tr('subtitle')}</p>
        </header>
        {style === 'grid' ? (
          <ShowcaseGrid discussions={discussions} />
        ) : (
          <ShowcaseCarousel discussions={discussions} />
        )}
      </section>
    );
  }
}
