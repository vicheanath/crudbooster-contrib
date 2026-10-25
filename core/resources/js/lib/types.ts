/**
 * The props every field island receives: Field::getComponentProps() on the PHP side, plus
 * `invalid`, which the Blade view adds when the field has a validation error.
 */
export interface FieldProps {
  name: string
  id: string
  label?: string
  required?: boolean
  disabled?: boolean
  placeholder?: string | null
  attributes?: Record<string, string | number | boolean>
  invalid?: boolean
}

/** An option of a Select, Radio or CheckboxList: a {value, label} pair (see Select::getComponentProps()). */
export interface Option {
  value: string
  label: string
}
