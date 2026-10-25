import { useEffect, useState } from 'react'
import cx from '../lib/cx'
import { t } from '../lib/lang'

const STORAGE_KEY = 'cb-theme'
const MODES = ['light', 'dark', 'system']
const ICONS = { light: 'fa fa-sun-o', dark: 'fa fa-moon-o', system: 'fa fa-adjust' }

function read() {
  try {
    return localStorage.getItem(STORAGE_KEY) || 'system'
  } catch {
    return 'system'
  }
}

/**
 * Applies a mode to <html>. The layout runs the same logic inline before the first paint.
 */
export function applyMode(mode) {
  const dark = mode === 'dark' || (mode === 'system' && window.matchMedia?.('(prefers-color-scheme: dark)').matches)
  document.documentElement.classList.toggle('dark', Boolean(dark))
}

export default function ThemeToggle() {
  const [mode, setMode] = useState(read)

  useEffect(() => {
    applyMode(mode)
    try {
      localStorage.setItem(STORAGE_KEY, mode)
    } catch {
      // Private mode: the choice lasts for this page only.
    }
    if (mode !== 'system' || !window.matchMedia) return undefined
    const query = window.matchMedia('(prefers-color-scheme: dark)')
    const follow = () => applyMode('system')
    query.addEventListener?.('change', follow)
    return () => query.removeEventListener?.('change', follow)
  }, [mode])

  return (
    <div className="flex rounded-lg bg-gray-100 p-0.5 dark:bg-white/10" role="radiogroup" aria-label="Theme">
      {MODES.map((option) => {
        const label = t(`theme${option[0].toUpperCase()}${option.slice(1)}`)
        return (
          <button
            key={option}
            type="button"
            role="radio"
            aria-checked={mode === option}
            title={label}
            aria-label={label}
            onClick={() => setMode(option)}
            className={cx(
              'flex size-7 cursor-pointer items-center justify-center rounded-md text-xs transition',
              mode === option ? 'bg-white text-gray-900 shadow-sm dark:bg-brand-500 dark:text-white' : 'text-gray-400 hover:text-gray-900 dark:text-gray-500 dark:hover:text-white',
            )}
          >
            <i className={ICONS[option]} aria-hidden="true" />
          </button>
        )
      })}
    </div>
  )
}
