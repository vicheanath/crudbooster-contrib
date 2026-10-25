/**
 * Translated strings from the layout's <script id="cb-panel-config">, with English defaults.
 */
const defaults = {
  search: "Search…",
  noResults: "No results",
  selectOption: "Select an option",
  clear: "Clear",
  remove: "Remove",
  undo: "Undo",
  selected: ":count selected",
  bulkActions: "Bulk actions",
  confirm: "Confirm",
  cancel: "Cancel",
  areYouSure: "Are you sure?",
  showPassword: "Show password",
  hidePassword: "Hide password",
  dropFile: "Drop a file here or click to browse",
  replaceFile: "Choose another file",
  fileTooLarge: "The file is larger than :size KB.",
  willBeRemoved: "Will be removed when you save.",
  themeLight: "Light",
  themeDark: "Dark",
  themeSystem: "System",
}

export type LangKey = keyof typeof defaults

let strings: Record<string, string> | null = null

export function t(key: LangKey, replace: Record<string, string | number> = {}): string {
  if (strings === null) {
    strings = { ...defaults }
    const config = typeof document !== "undefined" ? document.getElementById("cb-panel-config") : null
    if (config?.textContent) {
      try {
        Object.assign(strings, JSON.parse(config.textContent).lang ?? {})
      } catch {
        // Keep the defaults.
      }
    }
  }
  return Object.entries(replace).reduce((text, [name, value]) => text.replace(`:${name}`, String(value)), strings[key] ?? key)
}

/** For tests: forget the cached strings. */
export function resetLang(): void {
  strings = null
}
