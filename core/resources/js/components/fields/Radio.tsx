import { useState } from "react"
import { Label } from "@/components/ui/label"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { cn } from "@/lib/utils"
import type { FieldProps, Option } from "@/lib/types"

export interface RadioProps extends FieldProps {
  value?: string
  options?: Option[]
}

/**
 * Options as selectable cards. The chosen value is submitted from a hidden input.
 */
export default function Radio({ name, id, value = "", options = [], disabled, invalid }: RadioProps) {
  const [selected, setSelected] = useState(value == null ? "" : String(value))

  return (
    <>
      <input type="hidden" name={name} value={selected} disabled={disabled} />
      <RadioGroup
        aria-labelledby={`${id}-label`}
        value={selected}
        onValueChange={(next) => setSelected(String(next))}
        disabled={disabled}
        className="flex flex-wrap gap-2"
      >
        {options.map((option, index) => (
          <Label
            key={option.value}
            htmlFor={`${id}-${index}`}
            className={cn(
              "border-input cursor-pointer rounded-lg border px-3 py-2 font-normal transition-colors",
              "has-data-checked:border-primary has-data-checked:bg-primary/5 dark:has-data-checked:bg-primary/10",
              invalid && "border-destructive",
            )}
          >
            <RadioGroupItem id={`${id}-${index}`} value={option.value} aria-invalid={invalid || undefined} />
            {option.label}
          </Label>
        ))}
      </RadioGroup>
    </>
  )
}
