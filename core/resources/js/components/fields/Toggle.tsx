import { useState } from "react"
import { Label } from "@/components/ui/label"
import { Switch } from "@/components/ui/switch"
import type { FieldProps } from "@/lib/types"

export interface ToggleProps extends FieldProps {
  value?: boolean
}

/**
 * A switch. Submits 1 when on and 0 when off, so turning it off is saved too.
 */
export default function Toggle({ name, id, label, value = false, disabled }: ToggleProps) {
  const [on, setOn] = useState(Boolean(value))

  return (
    <div className="flex items-center gap-3">
      <input type="hidden" name={name} value={on ? "1" : "0"} disabled={disabled} />
      <Switch id={id} checked={on} onCheckedChange={(checked) => setOn(checked)} disabled={disabled} />
      <Label htmlFor={id} className="cursor-pointer">{label}</Label>
    </div>
  )
}
