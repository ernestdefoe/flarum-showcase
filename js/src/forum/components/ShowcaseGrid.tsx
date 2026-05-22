import Component from 'flarum/common/Component';
import type Discussion from 'flarum/common/models/Discussion';

import ShowcaseCard from './ShowcaseCard';

interface Attrs {
  discussions: Discussion[];
}

export default class ShowcaseGrid extends Component<Attrs> {
  view() {
    return (
      <div className="ShowcaseGrid">
        {this.attrs.discussions.map((d) => (
          <ShowcaseCard key={d.id()} discussion={d} />
        ))}
      </div>
    );
  }
}
