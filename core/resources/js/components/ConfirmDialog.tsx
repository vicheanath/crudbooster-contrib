import { useEffect, useState } from "react"
import { createRoot } from "react-dom/client"
import { CircleHelpIcon, TriangleAlertIcon } from "lucide-react"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogMedia,
  AlertDialogTitle,
} from "@/components/ui/alert-dialog"
import { t } from "@/lib/lang"

export interface ConfirmOptions {
  message?: string | null
  /** "danger" styles the confirm button as destructive. */
  color?: string | null
  confirmLabel?: string | null
}

interface OpenDialog extends ConfirmOptions {
  resolve: (confirmed: boolean) => void
}

let show: ((dialog: OpenDialog) => void) | null = null

/**
 * Asks for confirmation in a modal dialog. Resolves true when confirmed.
 */
export function confirmDialog(options: ConfirmOptions = {}): Promise<boolean> {
  if (!show && !document.getElementById("cb-dialog-root")) {
    const host = document.createElement("div")
    host.id = "cb-dialog-root"
    document.body.appendChild(host)
    createRoot(host).render(<DialogHost />)
  }
  return new Promise((resolve) => {
    // The host registers `show` once it has rendered.
    const open = () => (show ? show({ ...options, resolve }) : requestAnimationFrame(open))
    open()
  })
}

function DialogHost() {
  const [dialog, setDialog] = useState<OpenDialog | null>(null)
  const [open, setOpen] = useState(false)

  useEffect(() => {
    show = (next) => {
      setDialog(next)
      setOpen(true)
    }
    return () => {
      show = null
    }
  }, [])

  const close = (confirmed: boolean) => {
    if (!open) return
    dialog?.resolve(confirmed)
    setOpen(false)
  }

  const danger = dialog?.color === "danger"

  return (
    <AlertDialog open={open} onOpenChange={(next) => !next && close(false)}>
      <AlertDialogContent size="sm">
        <AlertDialogHeader>
          <AlertDialogMedia className={danger ? "bg-destructive/10 text-destructive" : undefined}>
            {danger ? <TriangleAlertIcon /> : <CircleHelpIcon />}
          </AlertDialogMedia>
          <AlertDialogTitle>{dialog?.message || t("areYouSure")}</AlertDialogTitle>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel onClick={() => close(false)}>{t("cancel")}</AlertDialogCancel>
          <AlertDialogAction variant={danger ? "destructive" : "default"} onClick={() => close(true)}>
            {dialog?.confirmLabel || t("confirm")}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  )
}

/**
 * Buttons and links with data-cb-confirm="message" ask first. A submit button is then
 * re-submitted with its own formaction; a link is followed.
 */
export function enableConfirmations(root: Document | HTMLElement = document): void {
  root.addEventListener(
    "click",
    async (event) => {
      const trigger = (event.target as Element | null)?.closest?.<HTMLElement>("[data-cb-confirm]")
      if (!trigger || trigger.dataset.cbConfirmed === "1") return
      event.preventDefault()
      event.stopPropagation()

      const confirmed = await confirmDialog({
        message: trigger.getAttribute("data-cb-confirm"),
        color: trigger.getAttribute("data-cb-confirm-color"),
        confirmLabel: trigger.getAttribute("data-cb-confirm-label"),
      })
      if (!confirmed) return

      if (trigger instanceof HTMLButtonElement && trigger.form) {
        trigger.dataset.cbConfirmed = "1"
        trigger.form.requestSubmit(trigger)
        delete trigger.dataset.cbConfirmed
      } else if (trigger instanceof HTMLAnchorElement && trigger.href) {
        window.location.assign(trigger.href)
      }
    },
    true,
  )
}
