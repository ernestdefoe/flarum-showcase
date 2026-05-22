import Admin from 'flarum/common/extenders/Admin';
import app from 'flarum/admin/app';
import type Tag from 'flarum/tags/common/models/Tag';

const t = (key: string) => app.translator.trans(`ernestdefoe-showcase.admin.settings.${key}`);

export default [
  new Admin()
    .setting(() => ({
      setting: 'ernestdefoe-showcase.display_style',
      type: 'select',
      options: {
        carousel: t('display_style.carousel'),
        grid: t('display_style.grid'),
      },
      label: t('display_style.label'),
      help: t('display_style.help'),
    }))
    .setting(() => ({
      setting: 'ernestdefoe-showcase.primary_tag_ids',
      type: 'flarum-tags.select-tags',
      label: t('primary_tag_ids.label'),
      help: t('primary_tag_ids.help'),
      options: {
        selectableTags: (tags: Tag[]) => tags.filter((tag) => tag.position() !== null),
        canSelect: () => true,
      },
    }))
    .setting(() => ({
      setting: 'ernestdefoe-showcase.secondary_tag_ids',
      type: 'flarum-tags.select-tags',
      label: t('secondary_tag_ids.label'),
      help: t('secondary_tag_ids.help'),
      options: {
        selectableTags: (tags: Tag[]) => tags.filter((tag) => tag.position() === null),
        canSelect: () => true,
      },
    }))
    .setting(() => ({
      setting: 'ernestdefoe-showcase.max_cards',
      type: 'number',
      label: t('max_cards.label'),
      help: t('max_cards.help'),
      min: 1,
      max: 100,
    }))
    .setting(() => ({
      setting: 'ernestdefoe-showcase.sort',
      type: 'select',
      options: {
        latest: t('sort.latest'),
        most_replied: t('sort.most_replied'),
        pinned_first: t('sort.pinned_first'),
      },
      label: t('sort.label'),
    })),
];
