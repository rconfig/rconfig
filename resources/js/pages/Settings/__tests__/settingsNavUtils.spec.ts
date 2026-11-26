import { describe, expect, it } from 'vitest';
import { normalizeSettingsPath } from '@/pages/Settings/settingsNavUtils';

describe('normalizeSettingsPath', () => {
  it('normalizes nested credentials path', () => {
    expect(normalizeSettingsPath('/settings/credentials/123')).toBe('/settings/credentials');
  });

  it('normalizes nested data purge path', () => {
    expect(normalizeSettingsPath('/settings/data-purge/run-history')).toBe('/settings/data-purge');
  });

  it('returns unchanged path for regular settings pane routes', () => {
    expect(normalizeSettingsPath('/settings/system')).toBe('/settings/system');
  });
});
