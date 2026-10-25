import TextInput from './TextInput'

const ICONS = { time: 'fa fa-clock-o', date: 'fa fa-calendar', 'datetime-local': 'fa fa-calendar' }

/**
 * The browser's own picker, which is accessible and localised, in the panel's input style.
 */
export default function DatePicker(props) {
  return <TextInput {...props} icon={ICONS[props.type] ?? ICONS.date} />
}
