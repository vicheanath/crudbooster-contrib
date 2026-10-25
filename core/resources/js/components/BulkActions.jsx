import { useEffect, useRef, useState } from 'react'
import cx from '../lib/cx'
import { t } from '../lib/lang'
import { confirmDialog } from './ConfirmDialog'

/**
 * The bulk action menu above the table. It counts the ticked rows (inputs matching `selector`),
 * drives the "select all" box, and submits `form` to the chosen action's URL.
 */
export default function BulkActions({ actions = [], form = 'cb-bulk-form', selector = '.cb-select', selectAll = 'cb-select-all' }) {
  const [count, setCount] = useState(0)
  const [open, setOpen] = useState(false)
  const menu = useRef(null)

  useEffect(() => {
    const boxes = () => Array.from(document.querySelectorAll(selector))
    const all = document.getElementById(selectAll)

    const refresh = () => {
      const ticked = boxes().filter((box) => box.checked).length
      setCount(ticked)
      if (all) {
        all.checked = ticked > 0 && ticked === boxes().length
        all.indeterminate = ticked > 0 && ticked < boxes().length
      }
    }
    const onChange = (event) => {
      if (event.target === all) boxes().forEach((box) => (box.checked = all.checked))
      if (event.target === all || event.target.matches?.(selector)) refresh()
    }

    document.addEventListener('change', onChange)
    refresh()
    return () => document.removeEventListener('change', onChange)
  }, [selector, selectAll])

  useEffect(() => {
    if (!open) return undefined
    const close = (event) => !menu.current?.contains(event.target) && setOpen(false)
    document.addEventListener('mousedown', close)
    return () => document.removeEventListener('mousedown', close)
  }, [open])

  const run = async (action) => {
    setOpen(false)
    if (action.confirm && !(await confirmDialog({ message: action.confirm, color: action.color }))) return
    const target = document.getElementById(form)
    target.action = action.url
    target.submit()
  }

  return (
    <div ref={menu} className="relative">
      <button
        type="button"
        id="cb-bulk-toggle"
        className="cb-btn cb-btn-sm"
        disabled={count === 0}
        aria-haspopup="menu"
        aria-expanded={open}
        onClick={() => setOpen((current) => !current)}
      >
        {count > 0 ? t('selected', { count }) : t('bulkActions')}
        <i className="fa fa-angle-down" aria-hidden="true" />
      </button>
      {open && (
        <div role="menu" className="absolute right-0 z-30 mt-1 min-w-48 overflow-hidden rounded-lg bg-white py-1 shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
          {actions.map((action) => (
            <button
              key={action.name}
              type="button"
              role="menuitem"
              onClick={() => run(action)}
              className={cx(
                'flex w-full cursor-pointer items-center gap-2 px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-white/5',
                action.color === 'danger' ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-200',
              )}
            >
              {action.icon && <i className={cx(action.icon, 'w-4 text-center')} aria-hidden="true" />}
              {action.label}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}
