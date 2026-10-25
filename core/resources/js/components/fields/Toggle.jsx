import { useState } from 'react'
import cx from '../../lib/cx'

/**
 * A switch. Submits 1 when on and 0 when off, so turning it off is saved too.
 */
export default function Toggle({ name, id, label, value = false, disabled }) {
  const [on, setOn] = useState(Boolean(value))

  return (
    <div className="flex items-center gap-3">
      <input type="hidden" name={name} value={on ? '1' : '0'} disabled={disabled} />
      <button
        type="button"
        id={id}
        role="switch"
        aria-checked={on}
        disabled={disabled}
        onClick={() => setOn((current) => !current)}
        className={cx(
          'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
          'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:opacity-50',
          on ? 'bg-brand-600' : 'bg-gray-200 dark:bg-white/15',
        )}
      >
        <span
          aria-hidden="true"
          className={cx(
            'pointer-events-none inline-block size-5 rounded-full bg-white shadow ring-0 transition-transform',
            on ? 'translate-x-5' : 'translate-x-0',
          )}
        />
      </button>
      <label htmlFor={id} className="cursor-pointer text-sm font-medium text-gray-900 dark:text-white">
        {label}
      </label>
    </div>
  )
}
