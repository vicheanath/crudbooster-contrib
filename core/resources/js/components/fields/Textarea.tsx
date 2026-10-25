import { Textarea as UiTextarea } from "@/components/ui/textarea"
import { htmlAttributes } from "@/lib/attributes"
import type { FieldProps } from "@/lib/types"

export interface TextareaProps extends FieldProps {
  value?: string
  rows?: number
}

export default function Textarea({ name, id, value = "", rows = 4, placeholder, required, disabled, invalid, attributes }: TextareaProps) {
  return (
    <UiTextarea
      {...htmlAttributes(attributes)}
      id={id}
      name={name}
      rows={rows}
      defaultValue={value ?? ""}
      placeholder={placeholder ?? undefined}
      required={required}
      disabled={disabled}
      aria-invalid={invalid || undefined}
    />
  )
}
