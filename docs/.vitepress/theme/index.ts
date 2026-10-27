import { h } from 'vue'
import DefaultTheme from 'vitepress/theme'
import DeprecatedBanner from './DeprecatedBanner.vue'

export default {
  extends: DefaultTheme,
  Layout: () => h(DefaultTheme.Layout, null, {
    'doc-before': () => h(DeprecatedBanner),
  }),
}
