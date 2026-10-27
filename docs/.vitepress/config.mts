import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import { defineConfig, type DefaultTheme } from 'vitepress'

const repo = 'https://github.com/vicheanath/crudbooster-contrib'

// Root files that docs link to with GitHub-relative paths, mapped to their site pages.
const rootPages: Record<string, string> = {
  'UPGRADE.md': '/en/upgrade',
  'CHANGELOG.md': '/en/changelog',
  'CONTRIBUTING.md': '/en/contributing',
}

/**
 * Builds the sidebar from the table of contents in docs/en/index.md, so that file stays the
 * single source of truth. Bold list items become groups; nested lists become nested groups.
 * Nested groups, and groups marked "(deprecated)", start collapsed.
 */
function sidebarFromIndex(): DefaultTheme.SidebarItem[] {
  const index = readFileSync(fileURLToPath(new URL('../en/index.md', import.meta.url)), 'utf8')
  const root: DefaultTheme.SidebarItem[] = []
  const stack: { indent: number; items: DefaultTheme.SidebarItem[] }[] = [{ indent: -1, items: root }]

  for (const line of index.split('\n')) {
    const match = line.match(/^(\s*)- (.*)$/)
    if (!match) continue
    const indent = match[1].length
    while (stack.length > 1 && indent <= stack[stack.length - 1].indent) stack.pop()
    const parent = stack[stack.length - 1].items
    const group = match[2].match(/^\*\*(.+)\*\*$/)
    const link = match[2].match(/^\[(.+)\]\(\.\/(.+)\.md\)$/)
    if (group) {
      const item: DefaultTheme.SidebarItem = { text: group[1], collapsed: indent > 0 || /\(deprecated\)/i.test(group[1]), items: [] }
      parent.push(item)
      stack.push({ indent, items: item.items! })
    } else if (link) {
      parent.push({ text: link[1], link: `/en/${link[2]}` })
    }
  }

  // Top-level links before the first group go into "Guide"; later loose links into "More Guides".
  const sidebar: DefaultTheme.SidebarItem[] = []
  let loose: DefaultTheme.SidebarItem | null = null
  for (const item of root) {
    if (item.items) {
      sidebar.push(item)
      loose = null
    } else {
      if (!loose) {
        loose = { text: sidebar.length ? 'More Guides' : 'Guide', items: [] }
        sidebar.push(loose)
      }
      loose.items!.push(item)
    }
  }

  sidebar.push({
    text: 'Project',
    items: [
      { text: 'Changelog', link: '/en/changelog' },
      { text: 'Contributing', link: '/en/contributing' },
    ],
  })

  return sidebar
}

export default defineConfig({
  title: 'CRUDBooster',
  description: 'Laravel CRUD generator: classic CRUDBooster on PHP 8.2+ and Laravel 12/13',
  base: '/crudbooster-contrib/',
  cleanUrls: true,
  lastUpdated: true,
  head: [['meta', { name: 'theme-color', content: '#dd4b39' }]],

  markdown: {
    config(md) {
      const render = md.renderer.rules.link_open ?? ((tokens, idx, options, _env, self) => self.renderToken(tokens, idx, options))
      md.renderer.rules.link_open = (tokens, idx, options, env, self) => {
        const href = tokens[idx].attrGet('href') ?? ''
        const [path, hash] = href.split('#')
        const file = path.split('/').pop() ?? ''
        // Root files (UPGRADE.md, ...) link to docs pages with repo-relative paths: docs/en/x.md -> /en/x.
        const docsPage = path.match(/^(?:\.\/)?docs\/(.+)\.md$/)
        const target = /^https?:/.test(href) ? null : rootPages[file] ?? (docsPage && `/${docsPage[1]}`)
        if (target) {
          tokens[idx].attrSet('href', target + (hash ? `#${hash}` : ''))
        }
        return render(tokens, idx, options, env, self)
      }
    },
  },

  themeConfig: {
    nav: [
      { text: 'Guide', link: '/en/installation' },
      { text: 'Table of Contents', link: '/en/' },
      {
        text: 'Migrate',
        items: [
          { text: 'Classic modules to resources', link: '/en/deprecations' },
          { text: 'CRUDBooster 5.x to 7.x', link: '/en/upgrade' },
        ],
      },
      { text: 'Changelog', link: '/en/changelog' },
      { text: 'Packagist', link: 'https://packagist.org/packages/vicheanath/crudbooster' },
    ],
    sidebar: { '/en/': sidebarFromIndex() },
    socialLinks: [{ icon: 'github', link: repo }],
    editLink: { pattern: `${repo}/edit/main/docs/:path`, text: 'Edit this page on GitHub' },
    search: { provider: 'local' },
    outline: [2, 3],
    footer: {
      message: 'Released under the MIT License.',
      copyright: 'Originally created by Ferry Ariawan · maintained as vicheanath/crudbooster',
    },
  },
})
