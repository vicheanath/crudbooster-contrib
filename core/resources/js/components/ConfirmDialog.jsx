import { useEffect, useRef, useState } from 'react'
import { createRoot } from 'react-dom/client'
import cx from '../lib/cx'
import { t } from '../lib/lang'

let open = null

/**
 * Asks for confirmation in a modal dialog. Resolves true when confirmed.
 */
export function confirmDialog(options) {
  if (!open) {
    const host = document.createElement('div')
    host.id = 'cb-dialog-root'
    document.body.appendChild(host)
    createRoot(host).render(<DialogHost />)
  }
  return new Promise((resolve) => {
    // The host registers `open` on its first render.
    const show = () => (open ? open({ ...options, resolve }) : requestAnimationFrame(show))
    show()
  })
}

function DialogHost() {
  const [dialog, setDialog] = useState(null)
  const confirmButton = useRef(null)

  useEffect(() => {
    open = setDialog
    return () => {
      open = null
    }
  }, [])

  useEffect(() => {
    if (!dialog) return undefined
    confirmButton.current?.focus()
    const onKey = (event) => event.key === 'Escape' && close(false)
    document.addEventListener('keydown', onKey)
    return () => document.removeEventListener('keydown', onKey)
  })

  if (!dialog) return null

  function close(result) {
    dialog.resolve(result)
    setDialog(null)
  }

  const danger = dialog.color === 'danger'

  return (
    <div className="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="presentation">
      <div className="absolute inset-0 bg-gray-900/50 backdrop-blur-[1px]" onClick={() => close(false)} />
      <div
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="cb-dialog-title"
        className="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800 dark:ring-1 dark:ring-white/10"
      >
        <div className="flex gap-4">
          <div className={cx('flex size-10 shrink-0 items-center justify-center rounded-full', danger ? 'bg-red-100 text-red-600 dark:bg-red-500/15' : 'bg-brand-100 text-brand-600 dark:bg-brand-500/15')}>
            <i className={danger ? 'fa fa-exclamation-triangle' : 'fa fa-question'} aria-hidden="true" />
          </div>
          <p id="cb-dialog-title" className="pt-2 text-sm text-gray-700 dark:text-gray-200">
            {dialog.message || t('areYouSure')}
          </p>
        </div>
        <div className="mt-6 flex justify-end gap-2">
          <button type="button" className="cb-btn" onClick={() => close(false)}>
            {t('cancel')}
          </button>
          <button
            ref={confirmButton}
            type="button"
            className={cx('cb-btn', danger ? 'cb-btn-danger' : 'cb-btn-primary')}
            onClick={() => close(true)}
          >
            {dialog.confirmLabel || t('confirm')}
          </button>
        </div>
      </div>
    </div>
  )
}

/**
 * Buttons and links with data-cb-confirm="message" ask first. A submit button is then
 * re-submitted with its own formaction; a link is followed.
 */
export function enableConfirmations(root = document) {
  root.addEventListener(
    'click',
    async (event) => {
      const trigger = event.target.closest?.('[data-cb-confirm]')
      if (!trigger || trigger.dataset.cbConfirmed === '1') return
      event.preventDefault()
      event.stopPropagation()

      const confirmed = await confirmDialog({
        message: trigger.getAttribute('data-cb-confirm'),
        color: trigger.getAttribute('data-cb-confirm-color') || undefined,
        confirmLabel: trigger.getAttribute('data-cb-confirm-label') || undefined,
      })
      if (!confirmed) return

      if (trigger.form) {
        trigger.dataset.cbConfirmed = '1'
        trigger.form.requestSubmit(trigger)
        delete trigger.dataset.cbConfirmed
      } else if (trigger.href) {
        window.location.assign(trigger.href)
      }
    },
    true,
  )
}
