<script setup lang="ts">
import { computed } from 'vue'
import { useData, withBase } from 'vitepress'

// Pages of the classic module layer set `deprecated:` in their frontmatter:
//   instead: what to use now (backticks become <code>)
//   migrate: the section of the migration guide that maps the old API
//   guide:   the page that documents the replacement (optional)
const { frontmatter } = useData()

const notice = computed(() => frontmatter.value.deprecated as
  | { instead?: string; migrate?: string; guide?: string }
  | undefined)

const escape = (text: string) =>
  text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')

const instead = computed(() =>
  escape(notice.value?.instead ?? '').replace(/`([^`]+)`/g, '<code>$1</code>'))
</script>

<template>
  <div v-if="notice" class="warning custom-block cb-deprecated" role="note">
    <p class="custom-block-title">Deprecated since 7.2</p>
    <p>
      This page documents the classic module layer (<code>CBController</code> and
      <code>cbInit()</code>). It still works in every 7.x release, but new code should use
      resources and pages.
    </p>
    <p v-if="notice.instead"><strong>Use instead:</strong> <span v-html="instead" /></p>
    <p class="cb-deprecated-links">
      <a :href="withBase(notice.migrate ?? '/en/deprecations')">How to migrate</a>
      <a v-if="notice.guide" :href="withBase(notice.guide)">The new guide</a>
    </p>
  </div>
</template>

<style scoped>
.cb-deprecated-links {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
}

.cb-deprecated-links a {
  font-weight: 600;
}

.cb-deprecated-links a::after {
  content: ' →';
}
</style>
