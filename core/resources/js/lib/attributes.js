const REACT_NAMES = {
  class: 'className',
  for: 'htmlFor',
  autocomplete: 'autoComplete',
  autofocus: 'autoFocus',
  readonly: 'readOnly',
  maxlength: 'maxLength',
  minlength: 'minLength',
  tabindex: 'tabIndex',
  inputmode: 'inputMode',
  spellcheck: 'spellCheck',
}

/**
 * Turns a field's extraAttributes() (HTML names) into React props.
 */
export function htmlAttributes(attributes = {}) {
  const props = {}
  for (const [name, value] of Object.entries(attributes || {})) {
    if (name === 'style' || name.startsWith('on')) continue // not representable safely
    props[REACT_NAMES[name.toLowerCase()] ?? name] = value
  }
  return props
}
