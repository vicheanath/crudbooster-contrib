import { useState } from "react"
import { Checkbox } from "@/components/ui/checkbox"
import { Label } from "@/components/ui/label"
import { cn } from "@/lib/utils"
import type { FieldProps, Option } from "@/lib/types"

export interface CheckboxListProps extends FieldProps {
  value?: string[]
  options?: Option[]
}

/**
 * Several values as checkboxes, submitted as name[]. An empty hidden input lets "none ticked"
 * be saved.
 */
export default function CheckboxList({ name, id, value = [], options = [], disabled, invalid }: CheckboxListProps) {
  const [checked, setChecked] = useState(() => new Set((value ?? []).map(String)))

  const toggle = (optionValue: string, on: boolean) =>
    setChecked((current) => {
      const next = new Set(current)
      if (on) next.add(optionValue)
      else next.delete(optionValue)
      return next
    })

  return (
    <div role="group" aria-labelledby={`${id}-label`} className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
      <input type="hidden" name={name} value="" disabled={disabled} />
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
          {checked.has(option.value) && <input type="hidden" name={`${name}[]`} value={option.value} disabled={disabled} />}
          <Checkbox
            id={`${id}-${index}`}
            checked={checked.has(option.value)}
            onCheckedChange={(on) => toggle(option.value, on)}
            disabled={disabled}
            aria-invalid={invalid || undefined}
          />
          {option.label}
        </Label>
      ))}
    </div>
  )
}
