import { htmlAttributes } from '../../lib/attributes'
import cx from '../../lib/cx'

export default function Textarea({ name, id, value = '', rows = 4, placeholder, required, disabled, invalid, attributes }) {
  return (
    <textarea
      {...htmlAttributes(attributes)}
      id={id}
      name={name}
      rows={rows}
      defaultValue={value ?? ''}
      placeholder={placeholder ?? undefined}
      required={required}
      disabled={disabled}
      aria-invalid={invalid || undefined}
      className={cx('cb-input', invalid && 'cb-input-invalid')}
    />
  )
}
