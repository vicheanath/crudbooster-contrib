import { useEffect, useId, useMemo, useRef, useState } from 'react'
import cx from '../../lib/cx'
import { t } from '../../lib/lang'

/**
 * A searchable combobox for one or several values. It submits hidden inputs named like a native
 * select: "name" for one value, "name[]" for several (or an empty "name" when none is chosen,
 * so clearing a many-to-many relationship is saved).
 */
export default function Select({
  name, id, value, options = [], multiple = false, placeholder, required, disabled, invalid,
}) {
  const [selected, setSelected] = useState(() =>
    multiple ? (Array.isArray(value) ? value.map(String) : []) : value == null ? '' : String(value),
  )
  const [open, setOpen] = useState(false)
  const [query, setQuery] = useState('')
  const [active, setActive] = useState(0)
  const container = useRef(null)
  const search = useRef(null)
  const listId = useId()

  const labels = useMemo(() => new Map(options.map((option) => [option.value, option.label])), [options])
  const filtered = useMemo(() => {
    const needle = query.trim().toLowerCase()
    return needle ? options.filter((option) => option.label.toLowerCase().includes(needle)) : options
  }, [options, query])

  const isSelected = (optionValue) => (multiple ? selected.includes(optionValue) : selected === optionValue)

  useEffect(() => {
    if (!open) return undefined
    search.current?.focus()
    const close = (event) => {
      if (!container.current?.contains(event.target)) setOpen(false)
    }
    document.addEventListener('mousedown', close)
    return () => document.removeEventListener('mousedown', close)
  }, [open])

  useEffect(() => setActive(0), [query])

  const choose = (optionValue) => {
    if (multiple) {
      setSelected((current) =>
        current.includes(optionValue) ? current.filter((item) => item !== optionValue) : [...current, optionValue],
      )
      setQuery('')
      search.current?.focus()
    } else {
      setSelected(optionValue)
      setOpen(false)
      setQuery('')
    }
  }

  const onKeyDown = (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault()
      if (!open) setOpen(true)
      else setActive((index) => Math.min(index + 1, filtered.length - 1))
    } else if (event.key === 'ArrowUp') {
      event.preventDefault()
      setActive((index) => Math.max(index - 1, 0))
    } else if (event.key === 'Enter' && open) {
      event.preventDefault()
      if (filtered[active]) choose(filtered[active].value)
    } else if (event.key === 'Escape' && open) {
      event.preventDefault()
      setOpen(false)
    } else if (event.key === 'Backspace' && multiple && query === '' && selected.length) {
      setSelected((current) => current.slice(0, -1))
    }
  }

  const summary = multiple ? null : labels.get(selected)

  return (
    <div ref={container} className="relative" onKeyDown={onKeyDown}>
      {multiple ? (
        selected.length ? (
          selected.map((item) => <input key={item} type="hidden" name={`${name}[]`} value={item} disabled={disabled} />)
        ) : (
          <input type="hidden" name={name} value="" disabled={disabled} />
        )
      ) : (
        <input type="hidden" name={name} value={selected} disabled={disabled} />
      )}

      <div
        className={cx(
          'cb-input flex min-h-9 flex-wrap items-center gap-1.5 pr-9',
          multiple && selected.length && 'py-1.5',
          invalid && 'cb-input-invalid',
          disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer',
        )}
        onClick={() => !disabled && setOpen(true)}
      >
        {multiple &&
          selected.map((item) => (
            <span key={item} className="inline-flex items-center gap-1 rounded-md bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-inset ring-brand-600/20 dark:bg-brand-500/15 dark:text-brand-300">
              {labels.get(item) ?? item}
              {!disabled && (
                <button
                  type="button"
                  className="cursor-pointer rounded hover:text-brand-900 dark:hover:text-white"
                  aria-label={`${t('remove')} ${labels.get(item) ?? item}`}
                  onClick={(event) => {
                    event.stopPropagation()
                    choose(item)
                  }}
                >
                  <i className="fa fa-times" aria-hidden="true" />
                </button>
              )}
            </span>
          ))}
        <button
          type="button"
          id={id}
          role="combobox"
          aria-expanded={open}
          aria-controls={listId}
          aria-haspopup="listbox"
          aria-invalid={invalid || undefined}
          aria-required={required || undefined}
          disabled={disabled}
          className={cx(
            'min-w-0 flex-1 cursor-pointer truncate text-left outline-none disabled:cursor-not-allowed',
            !summary && 'text-gray-400 dark:text-gray-500',
          )}
          onClick={(event) => {
            event.stopPropagation()
            setOpen((current) => !current)
          }}
        >
          {summary ?? (multiple && selected.length ? '' : placeholder || t('selectOption'))}
        </button>
        <span className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
          <i className="fa fa-angle-down" aria-hidden="true" />
        </span>
        {!multiple && !required && selected !== '' && !disabled && (
          <button
            type="button"
            className="absolute inset-y-0 right-7 flex cursor-pointer items-center px-1 text-gray-400 hover:text-gray-600"
            aria-label={t('clear')}
            onClick={(event) => {
              event.stopPropagation()
              setSelected('')
            }}
          >
            <i className="fa fa-times-circle" aria-hidden="true" />
          </button>
        )}
      </div>

      {open && (
        <div className="absolute z-30 mt-1 w-full overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
          <div className="border-b border-gray-100 p-2 dark:border-white/10">
            <input
              ref={search}
              type="search"
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder={t('search')}
              aria-label={t('search')}
              aria-controls={listId}
              aria-activedescendant={filtered[active] ? `${listId}-${active}` : undefined}
              className="cb-input"
            />
          </div>
          <ul id={listId} role="listbox" aria-multiselectable={multiple || undefined} className="max-h-60 overflow-auto py-1 text-sm">
            {filtered.length === 0 && <li className="px-3 py-2 text-gray-500 dark:text-gray-400">{t('noResults')}</li>}
            {filtered.map((option, index) => (
              <li
                key={option.value}
                id={`${listId}-${index}`}
                role="option"
                aria-selected={isSelected(option.value)}
                onMouseEnter={() => setActive(index)}
                onMouseDown={(event) => event.preventDefault()}
                onClick={() => choose(option.value)}
                className={cx(
                  'flex cursor-pointer items-center justify-between px-3 py-2',
                  index === active ? 'bg-brand-600 text-white' : 'text-gray-900 dark:text-gray-100',
                )}
              >
                <span className="truncate">{option.label}</span>
                {isSelected(option.value) && <i className="fa fa-check" aria-hidden="true" />}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  )
}
