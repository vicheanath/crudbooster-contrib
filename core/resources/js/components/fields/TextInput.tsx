import { useState, type ReactNode } from "react"
import { EyeIcon, EyeOffIcon } from "lucide-react"
import { Input } from "@/components/ui/input"
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput, InputGroupText } from "@/components/ui/input-group"
import { htmlAttributes } from "@/lib/attributes"
import { t } from "@/lib/lang"
import type { FieldProps } from "@/lib/types"

export interface TextInputProps extends FieldProps {
  value?: string
  type?: string
  prefix?: string | null
  suffix?: string | null
  maxLength?: number | null
  /** An icon before the input (DatePicker passes one). */
  icon?: ReactNode
}

export default function TextInput({
  name, id, value = "", type = "text", placeholder, required, disabled, invalid,
  prefix, suffix, maxLength, attributes, icon,
}: TextInputProps) {
  const [revealed, setRevealed] = useState(false)
  const isPassword = type === "password"
  const extra = htmlAttributes(attributes)

  const inputProps = {
    ...extra,
    id,
    name,
    type: isPassword && revealed ? "text" : type,
    defaultValue: value ?? "",
    placeholder: placeholder ?? undefined,
    required,
    disabled,
    maxLength: maxLength ?? undefined,
    step: type === "number" ? "any" : undefined,
    autoComplete: isPassword ? "new-password" : (extra.autoComplete as string | undefined),
    "aria-invalid": invalid || undefined,
  }

  if (!prefix && !suffix && !isPassword && !icon) {
    return <Input {...inputProps} className={type === "color" ? "h-8 w-16 cursor-pointer p-1" : undefined} />
  }

  return (
    <InputGroup data-disabled={disabled || undefined}>
      <InputGroupInput {...inputProps} />
      {(icon || prefix) && (
        <InputGroupAddon>
          {icon}
          {prefix && <InputGroupText>{prefix}</InputGroupText>}
        </InputGroupAddon>
      )}
      {(suffix || isPassword) && (
        <InputGroupAddon align="inline-end">
          {suffix && <InputGroupText>{suffix}</InputGroupText>}
          {isPassword && (
            <InputGroupButton
              size="icon-xs"
              onClick={() => setRevealed((shown) => !shown)}
              aria-label={revealed ? t("hidePassword") : t("showPassword")}
              title={revealed ? t("hidePassword") : t("showPassword")}
            >
              {revealed ? <EyeOffIcon /> : <EyeIcon />}
            </InputGroupButton>
          )}
        </InputGroupAddon>
      )}
    </InputGroup>
  )
}
