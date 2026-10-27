import { useEffect, useState } from "react"
import { ChevronDownIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { t } from "@/lib/lang"
import { confirmDialog } from "./ConfirmDialog"

export interface BulkAction {
  name: string
  label: string
  url: string
  /** A Font Awesome class, as set with Action::icon(). */
  icon?: string | null
  color?: string | null
  confirm?: string | null
}

export interface BulkActionsProps {
  actions?: BulkAction[]
  /** The form the ticked rows belong to. */
  form?: string
  /** The row checkboxes. */
  selector?: string
  /** The "select all" checkbox. */
  selectAll?: string
}

/**
 * The bulk action menu above the table. It counts the ticked rows, drives the "select all" box,
 * and submits `form` to the chosen action's URL.
 */
export default function BulkActions({ actions = [], form = "cb-bulk-form", selector = ".cb-select", selectAll = "cb-select-all" }: BulkActionsProps) {
  const [count, setCount] = useState(0)

  useEffect(() => {
    const boxes = () => Array.from(document.querySelectorAll<HTMLInputElement>(selector))
    const all = document.getElementById(selectAll) as HTMLInputElement | null

    const refresh = () => {
      const ticked = boxes().filter((box) => box.checked).length
      setCount(ticked)
      if (all) {
        all.checked = ticked > 0 && ticked === boxes().length
        all.indeterminate = ticked > 0 && ticked < boxes().length
      }
    }
    const onChange = (event: Event) => {
      const target = event.target as HTMLElement
      if (target === all) boxes().forEach((box) => (box.checked = all.checked))
      if (target === all || target.matches?.(selector)) refresh()
    }

    document.addEventListener("change", onChange)
    refresh()
    return () => document.removeEventListener("change", onChange)
  }, [selector, selectAll])

  const run = async (action: BulkAction) => {
    if (action.confirm && !(await confirmDialog({ message: action.confirm, color: action.color }))) return
    const target = document.getElementById(form) as HTMLFormElement | null
    if (!target) return
    target.action = action.url
    target.submit()
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger render={<Button variant="outline" size="sm" id="cb-bulk-toggle" disabled={count === 0} />}>
        {count > 0 ? t("selected", { count }) : t("bulkActions")}
        <ChevronDownIcon />
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-auto min-w-48">
        {actions.map((action) => (
          <DropdownMenuItem key={action.name} variant={action.color === "danger" ? "destructive" : "default"} onClick={() => run(action)}>
            {action.icon && <i className={`${action.icon} w-4 text-center`} aria-hidden="true" />}
            {action.label}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
