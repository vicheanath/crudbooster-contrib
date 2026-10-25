const REACT_NAMES: Record<string, string> = {
  class: "className",
  for: "htmlFor",
  autocomplete: "autoComplete",
  autofocus: "autoFocus",
  readonly: "readOnly",
  maxlength: "maxLength",
  minlength: "minLength",
  tabindex: "tabIndex",
  inputmode: "inputMode",
  spellcheck: "spellCheck",
}

/**
 * Turns a field's extraAttributes() (HTML names) into React props. Inline styles and event
 * handler attributes are left out: they cannot be passed safely as strings.
 */
export function htmlAttributes(attributes: Record<string, string | number | boolean> = {}): Record<string, string | number | boolean> {
  const props: Record<string, string | number | boolean> = {}
  for (const [name, value] of Object.entries(attributes ?? {})) {
    if (name === "style" || name.toLowerCase().startsWith("on")) continue
    props[REACT_NAMES[name.toLowerCase()] ?? name] = value
  }
  return props
}
