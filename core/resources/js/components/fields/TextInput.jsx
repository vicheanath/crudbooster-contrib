import { useState } from 'react'
import { htmlAttributes } from '../../lib/attributes'
import cx from '../../lib/cx'
import { t } from '../../lib/lang'

export default function TextInput({
  name, id, value = '', type = 'text', placeholder, required, disabled, invalid,
  prefix, suffix, maxLength, attributes, icon,
}) {
  const [revealed, setRevealed] = useState(false)
  const isPassword = type === 'password'
  const adorned = prefix || suffix || isPassword || icon

  const input = (
    <input
      {...htmlAttributes(attributes)}
      id={id}
      name={name}
      type={isPassword && revealed ? 'text' : type}
      defaultValue={value ?? ''}
      placeholder={placeholder ?? undefined}
      required={required}
      disabled={disabled}
      maxLength={maxLength ?? undefined}
      step={type === 'number' ? 'any' : undefined}
      autoComplete={isPassword ? 'new-password' : htmlAttributes(attributes).autoComplete}
      aria-invalid={invalid || undefined}
      className={cx(
        'cb-input',
        adorned && 'cb-input-bare',
        type === 'color' && 'h-10 cursor-pointer p-1',
        invalid && !adorned && 'cb-input-invalid',
      )}
    />
  )

  if (!adorned) return input

  return (
    <div className={cx('cb-input-group', invalid && 'cb-input-invalid', disabled && 'opacity-60')}>
      {icon && <span className="cb-input-addon" aria-hidden="true"><i className={icon} /></span>}
      {prefix && <span className="cb-input-addon">{prefix}</span>}
      {input}
      {suffix && <span className="cb-input-addon">{suffix}</span>}
      {isPassword && (
        <button
          type="button"
          className="cb-input-addon cursor-pointer hover:text-gray-900 dark:hover:text-white"
          onClick={() => setRevealed((shown) => !shown)}
          aria-label={revealed ? t('hidePassword') : t('showPassword')}
          title={revealed ? t('hidePassword') : t('showPassword')}
        >
          <i className={revealed ? 'fa fa-eye-slash' : 'fa fa-eye'} aria-hidden="true" />
        </button>
      )}
    </div>
  )
}
