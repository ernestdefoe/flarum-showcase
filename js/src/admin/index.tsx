import Admin from 'flarum/common/extenders/Admin';
import app from 'flarum/admin/app';
import SelectTagsSettingComponent from 'flarum/tags/admin/components/SelectTagsSettingComponent';
import type Tag from 'flarum/tags/common/models/Tag';
import type ExtensionPage from 'flarum/admin/components/ExtensionPage';

const t = (key: string) => app.translator.trans(`ernestdefoe-showcase.admin.settings.${key}`);

// Render `SelectTagsSettingComponent` directly via `customSetting`
// instead of relying on `type: 'flarum-tags.select-tags'` dispatch on a
// FormGroup. The string-type path silently falls back to a plain text
// <input> if flarum/tags' custom-field-component registration hasn't
// landed by render time — easy for admins to mistake for a working
// field and type raw IDs into. Calling the component directly removes
// that failure mode.
function tagPicker(
  page: ExtensionPage,
  settingKey: string,
  labelKey: string,
  helpKey: string,
  isSelectable: (tag: Tag) => boolean
) {
  return (
    <SelectTagsSettingComponent
      type="flarum-tags.select-tags"
      settingValue={page.setting(settingKey, '[]')}
      label={t(labelKey)}
      help={t(helpKey)}
      options={{
        selectableTags: (tags: Tag[]) => tags.filter(isSelectable),
        canSelect: () => true,
      }}
    />
  );
}

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
    .customSetting(function (this: ExtensionPage) {
      return tagPicker(
        this,
        'ernestdefoe-showcase.primary_tag_ids',
        'primary_tag_ids.label',
        'primary_tag_ids.help',
        (tag) => tag.position() !== null
      );
    })
    .customSetting(function (this: ExtensionPage) {
      return tagPicker(
        this,
        'ernestdefoe-showcase.secondary_tag_ids',
        'secondary_tag_ids.label',
        'secondary_tag_ids.help',
        (tag) => tag.position() === null
      );
    })
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
