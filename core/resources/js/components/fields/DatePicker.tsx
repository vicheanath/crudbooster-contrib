import { CalendarIcon, ClockIcon } from "lucide-react"
import TextInput, { type TextInputProps } from "./TextInput"

/**
 * The browser's own picker, which is accessible and localised, in the panel's input style.
 */
export default function DatePicker(props: TextInputProps) {
  return <TextInput {...props} icon={props.type === "time" ? <ClockIcon /> : <CalendarIcon />} />
}
