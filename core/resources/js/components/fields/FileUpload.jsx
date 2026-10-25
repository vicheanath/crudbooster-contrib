import { useEffect, useRef, useState } from 'react'
import cx from '../../lib/cx'
import { t } from '../../lib/lang'

/**
 * A drop zone around a real <input type="file">, so the file is uploaded with the form. Shows
 * the current file, a preview of the new one, and "Remove", which submits removeName=1.
 */
export default function FileUpload({
  name, id, accept, image = false, fileUrl, fileName, removeName, maxSize, required, disabled, invalid,
}) {
  const input = useRef(null)
  const [file, setFile] = useState(null)
  const [preview, setPreview] = useState(null)
  const [removing, setRemoving] = useState(false)
  const [dragging, setDragging] = useState(false)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!file || !image || !file.type?.startsWith('image/') || typeof URL.createObjectURL !== 'function') {
      setPreview(null)
      return undefined
    }
    const url = URL.createObjectURL(file)
    setPreview(url)
    return () => URL.revokeObjectURL(url)
  }, [file, image])

  const accepted = (files) => {
    const chosen = files?.[0] ?? null
    if (chosen && maxSize && chosen.size > maxSize * 1024) {
      setError(t('fileTooLarge', { size: maxSize }))
      if (input.current) input.current.value = ''
      setFile(null)
      return
    }
    setError(null)
    setFile(chosen)
    if (chosen) setRemoving(false)
  }

  const onDrop = (event) => {
    event.preventDefault()
    setDragging(false)
    if (disabled || !event.dataTransfer?.files?.length) return
    try {
      input.current.files = event.dataTransfer.files
    } catch {
      // Older browsers cannot assign a FileList; the preview still shows the dropped file.
    }
    accepted(event.dataTransfer.files)
  }

  const clear = () => {
    if (input.current) input.current.value = ''
    setFile(null)
    setError(null)
  }

  return (
    <div className="space-y-3">
      {fileUrl && !file && (
        <div className={cx('flex items-center gap-3 rounded-lg p-2 ring-1 ring-inset ring-gray-200 dark:ring-white/10', removing && 'opacity-60')}>
          {image ? (
            <a href={fileUrl} target="_blank" rel="noopener noreferrer">
              <img src={fileUrl} alt="" className={cx('size-16 rounded-md object-cover', removing && 'grayscale')} />
            </a>
          ) : (
            <a href={fileUrl} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm text-brand-600 hover:underline dark:text-brand-400">
              <i className="fa fa-file-o" aria-hidden="true" /> {fileName}
            </a>
          )}
          <div className="min-w-0 flex-1 text-sm">
            {image && <div className="truncate text-gray-700 dark:text-gray-300">{fileName}</div>}
            {removing && <div className="text-red-600 dark:text-red-400">{t('willBeRemoved')}</div>}
          </div>
          {!disabled && removeName && (
            <button type="button" className="cb-btn cb-btn-sm" onClick={() => setRemoving((current) => !current)}>
              {removing ? t('undo') : t('remove')}
            </button>
          )}
          {removing && <input type="hidden" name={removeName} value="1" />}
        </div>
      )}

      <label
        htmlFor={id}
        onDragOver={(event) => {
          event.preventDefault()
          if (!disabled) setDragging(true)
        }}
        onDragLeave={() => setDragging(false)}
        onDrop={onDrop}
        className={cx(
          'flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed px-4 py-6 text-center text-sm transition',
          dragging ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-gray-300 hover:border-brand-400 dark:border-white/15',
          invalid && 'border-red-400 dark:border-red-500/60',
          disabled && 'cursor-not-allowed opacity-60',
        )}
      >
        {preview ? (
          <img src={preview} alt="" className="max-h-40 rounded-md object-contain" />
        ) : (
          <i className={cx('fa text-2xl text-gray-400', image ? 'fa-picture-o' : 'fa-cloud-upload')} aria-hidden="true" />
        )}
        <span className="text-gray-600 dark:text-gray-300">
          {file ? file.name : fileUrl ? t('replaceFile') : t('dropFile')}
        </span>
        <input
          ref={input}
          id={id}
          type="file"
          name={name}
          accept={accept ?? undefined}
          required={required && !fileUrl}
          disabled={disabled}
          aria-invalid={invalid || undefined}
          className="sr-only"
          onChange={(event) => accepted(event.target.files)}
        />
      </label>

      {file && (
        <button type="button" className="text-sm text-gray-600 hover:underline dark:text-gray-300" onClick={clear}>
          <i className="fa fa-times" aria-hidden="true" /> {t('clear')}
        </button>
      )}
      {error && <p className="cb-error" role="alert">{error}</p>}
    </div>
  )
}
