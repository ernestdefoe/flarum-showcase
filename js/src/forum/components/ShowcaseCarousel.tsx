import Component from 'flarum/common/Component';
import app from 'flarum/forum/app';
import extractText from 'flarum/common/utils/extractText';
import type Discussion from 'flarum/common/models/Discussion';

import ShowcaseCard from './ShowcaseCard';

interface Attrs {
  discussions: Discussion[];
}

export default class ShowcaseCarousel extends Component<Attrs> {
  private trackEl: HTMLElement | null = null;

  oncreate(vnode: { dom: Element }) {
    super.oncreate(vnode);
    this.trackEl = (vnode.dom as HTMLElement).querySelector('.ShowcaseCarousel-track');
  }

  private scroll(dir: -1 | 1) {
    if (!this.trackEl) return;
    const firstCard = this.trackEl.querySelector('.ShowcaseCard') as HTMLElement | null;
    const step = firstCard ? firstCard.getBoundingClientRect().width + 14 : 300;
    this.trackEl.scrollBy({ left: dir * step * 2, behavior: 'smooth' });
  }

  view() {
    const prevLabel = extractText(app.translator.trans('ernestdefoe-showcase.lib.carousel.previous'));
    const nextLabel = extractText(app.translator.trans('ernestdefoe-showcase.lib.carousel.next'));

    return (
      <div className="ShowcaseCarousel">
        <button
          type="button"
          className="ShowcaseCarousel-nav ShowcaseCarousel-nav--prev"
          aria-label={prevLabel}
          title={prevLabel}
          onclick={() => this.scroll(-1)}
        >
          ‹
        </button>
        <button
          type="button"
          className="ShowcaseCarousel-nav ShowcaseCarousel-nav--next"
          aria-label={nextLabel}
          title={nextLabel}
          onclick={() => this.scroll(1)}
        >
          ›
        </button>
        <div className="ShowcaseCarousel-track">
          {this.attrs.discussions.map((d) => (
            <ShowcaseCard key={d.id()} discussion={d} />
          ))}
        </div>
      </div>
    );
  }
}
