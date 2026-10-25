import cx from '../../lib/cx'

/**
 * Options as selectable cards.
 */
export default function Radio({ name, id, value = '', options = [], disabled, required }) {
  return (
    <div role="radiogroup" aria-labelledby={`${id}-label`} className="flex flex-wrap gap-2">
      {options.map((option, index) => (
        <label
          key={option.value}
          className={cx(
            'flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-inset ring-gray-300 transition',
            'has-checked:bg-brand-50 has-checked:text-brand-700 has-checked:ring-brand-600 dark:ring-white/15',
            'dark:has-checked:bg-brand-500/15 dark:has-checked:text-brand-300 dark:has-checked:ring-brand-400',
            disabled && 'cursor-not-allowed opacity-60',
          )}
        >
          <input
            type="radio"
            id={`${id}-${index}`}
            name={name}
            value={option.value}
            defaultChecked={String(value) === option.value}
            disabled={disabled}
            required={required && index === 0}
            className="size-4 accent-brand-600"
          />
          {option.label}
        </label>
      ))}
    </div>
  )
}
