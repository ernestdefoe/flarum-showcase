import Component from 'flarum/common/Component';
import app from 'flarum/forum/app';
import extractText from 'flarum/common/utils/extractText';
import type Discussion from 'flarum/common/models/Discussion';

import { splitTags } from '../util';

interface Attrs {
  discussion: Discussion;
}

/**
 * Stable gradient for the synthesized fallback cover. Hashes the slug so
 * the same extension always gets the same colors — no jitter on refresh.
 */
function fallbackGradient(seed: string): string {
  let h = 0;
  for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) >>> 0;
  const hue1 = h % 360;
  const hue2 = (hue1 + 40) % 360;
  return `linear-gradient(135deg, hsl(${hue1} 55% 50%), hsl(${hue2} 60% 30%))`;
}

export default class ShowcaseCard extends Component<Attrs> {
  private username(d: Discussion): string {
    const u = d.user();
    if (u) return u.username();
    return extractText(app.translator.trans('ernestdefoe-showcase.lib.card.deleted_author'));
  }

  private replyCount(d: Discussion): string {
    const count = Math.max(0, (d.commentCount() ?? 1) - 1);
    return String(app.translator.trans('core.forum.discussion_list.replies_text', { count }));
  }

  view() {
    const d = this.attrs.discussion;
    const { primary, secondary } = splitTags(d);

    const coverUrl = d.attribute<string | null>('showcaseCoverUrl');
    const href = app.route.discussion(d);
    const primarySlug = primary ? String(primary.slug()) : '';
    const secondarySlug = secondary ? String(secondary.slug()).toLowerCase() : '';

    return (
      <a className="ShowcaseCard" href={href} config={m.route.link}>
        <div
          className="ShowcaseCard-cover"
          style={coverUrl ? { backgroundImage: `url("${coverUrl}")` } : { background: fallbackGradient(primarySlug || d.title()) }}
        >
          {!coverUrl && (
            <span className="ShowcaseCard-cover-initial" aria-hidden="true">
              {(primarySlug || d.title() || '?').charAt(0).toUpperCase()}
            </span>
          )}
          <div className="ShowcaseCard-tags">
            {secondary && (
              <span className={`ShowcaseCard-tag ShowcaseCard-tag--secondary tag--${secondarySlug}`}>
                {secondary.name()}
              </span>
            )}
            {primary && (
              <span className="ShowcaseCard-tag ShowcaseCard-tag--primary">{primary.name()}</span>
            )}
          </div>
        </div>
        <div className="ShowcaseCard-body">
          <h3 className="ShowcaseCard-title">{d.title()}</h3>
          <div className="ShowcaseCard-meta">
            <span>
              <strong>{this.username(d)}</strong>
            </span>
            <span className="ShowcaseCard-dot">·</span>
            <span>{this.replyCount(d)}</span>
          </div>
        </div>
      </a>
    );
  }
}
