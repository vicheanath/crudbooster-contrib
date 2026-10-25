import { useState } from 'react'
import cx from '../../lib/cx'

/**
 * Several values as checkboxes. An empty hidden input lets "none ticked" be saved.
 */
export default function CheckboxList({ name, id, value = [], options = [], disabled }) {
  const [checked, setChecked] = useState(() => new Set((value || []).map(String)))

  const toggle = (optionValue) =>
    setChecked((current) => {
      const next = new Set(current)
      next.has(optionValue) ? next.delete(optionValue) : next.add(optionValue)
      return next
    })

  return (
    <div role="group" aria-labelledby={`${id}-label`} className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
      <input type="hidden" name={name} value="" disabled={disabled} />
      {options.map((option, index) => (
        <label
          key={option.value}
          className={cx(
            'flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-inset ring-gray-200 dark:ring-white/10',
            checked.has(option.value) && 'bg-brand-50 ring-brand-600/40 dark:bg-brand-500/10',
            disabled && 'cursor-not-allowed opacity-60',
          )}
        >
          <input
            type="checkbox"
            id={`${id}-${index}`}
            name={`${name}[]`}
            value={option.value}
            checked={checked.has(option.value)}
            onChange={() => toggle(option.value)}
            disabled={disabled}
            className="size-4 rounded accent-brand-600"
          />
          {option.label}
        </label>
      ))}
    </div>
  )
}
