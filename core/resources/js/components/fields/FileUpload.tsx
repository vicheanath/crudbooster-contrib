import { useEffect, useRef, useState, type DragEvent } from "react"
import { FileIcon, ImageIcon, UploadIcon, XIcon } from "lucide-react"
import { Button } from "@/components/ui/button"
import { t } from "@/lib/lang"
import type { FieldProps } from "@/lib/types"
import { safeUrl } from "@/lib/url"
import { cn } from "@/lib/utils"

export interface FileUploadProps extends FieldProps {
  accept?: string | null
  image?: boolean
  fileUrl?: string | null
  fileName?: string | null
  removeName?: string | null
  maxSize?: number | null
}

/**
 * A drop zone around a real <input type="file">, so the file is uploaded with the form. Shows
 * the current file, a preview of the new one, and "Remove", which submits removeName=1.
 */
export default function FileUpload({
  name, id, accept, image = false, fileUrl: rawFileUrl, fileName, removeName, maxSize, required, disabled, invalid,
}: FileUploadProps) {
  const fileUrl = safeUrl(rawFileUrl)
  const input = useRef<HTMLInputElement>(null)
  const canvas = useRef<HTMLCanvasElement>(null)
  const [file, setFile] = useState<File | null>(null)
  const [preview, setPreview] = useState(false)
  const [removing, setRemoving] = useState(false)
  const [dragging, setDragging] = useState(false)
  const [error, setError] = useState<string | null>(null)

  // The preview is drawn on a canvas from the decoded image, so no URL or text from the file reaches the DOM.
  useEffect(() => {
    setPreview(false)
    if (!file || !image || !file.type?.startsWith("image/") || typeof createImageBitmap !== "function") return undefined

    let cancelled = false
    createImageBitmap(file)
      .then((bitmap) => {
        const target = canvas.current
        if (cancelled || !target) return bitmap.close?.()
        const scale = Math.min(1, 160 / bitmap.height, 320 / bitmap.width)
        target.width = Math.round(bitmap.width * scale)
        target.height = Math.round(bitmap.height * scale)
        target.getContext("2d")?.drawImage(bitmap, 0, 0, target.width, target.height)
        bitmap.close?.()
        setPreview(true)
      })
      .catch(() => setPreview(false))

    return () => {
      cancelled = true
    }
  }, [file, image])

  const accepted = (files: FileList | null) => {
    const chosen = files?.[0] ?? null
    if (chosen && maxSize && chosen.size > maxSize * 1024) {
      setError(t("fileTooLarge", { size: maxSize }))
      if (input.current) input.current.value = ""
      setFile(null)
      return
    }
    setError(null)
    setFile(chosen)
    if (chosen) setRemoving(false)
  }

  const onDrop = (event: DragEvent<HTMLLabelElement>) => {
    event.preventDefault()
    setDragging(false)
    if (disabled || !event.dataTransfer?.files?.length) return
    try {
      if (input.current) input.current.files = event.dataTransfer.files
    } catch {
      // Older browsers cannot assign a FileList; the dropped file is still shown.
    }
    accepted(event.dataTransfer.files)
  }

  const clear = () => {
    if (input.current) input.current.value = ""
    setFile(null)
    setError(null)
  }

  return (
    <div className="space-y-3">
      {fileUrl && !file && (
        <div className={cn("border-border flex items-center gap-3 rounded-lg border p-2", removing && "opacity-60")}>
          {image ? (
            <a href={fileUrl} target="_blank" rel="noopener noreferrer">
              <img src={fileUrl} alt="" className={cn("size-16 rounded-md object-cover", removing && "grayscale")} />
            </a>
          ) : (
            <a href={fileUrl} target="_blank" rel="noopener noreferrer" className="flex items-center gap-2 text-sm underline-offset-4 hover:underline">
              <FileIcon className="size-4" /> {fileName}
            </a>
          )}
          <div className="min-w-0 flex-1 text-sm">
            {image && <div className="truncate">{fileName}</div>}
            {removing && <div className="text-destructive">{t("willBeRemoved")}</div>}
          </div>
          {!disabled && removeName && (
            <Button type="button" variant="outline" size="sm" onClick={() => setRemoving((current) => !current)}>
              {removing ? t("undo") : t("remove")}
            </Button>
          )}
          {removing && removeName && <input type="hidden" name={removeName} value="1" />}
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
        className={cn(
          "border-input text-muted-foreground flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-dashed px-4 py-6 text-center text-sm transition-colors",
          dragging ? "border-ring bg-muted" : "hover:bg-muted/50",
          invalid && "border-destructive",
          disabled && "cursor-not-allowed opacity-50",
        )}
      >
        <canvas ref={canvas} aria-hidden="true" className={cn("max-h-40 rounded-md", !preview && "hidden")} />
        {!preview && (image ? <ImageIcon className="size-6" /> : <UploadIcon className="size-6" />)}
        <span className="text-foreground">{file ? file.name : fileUrl ? t("replaceFile") : t("dropFile")}</span>
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
        <Button type="button" variant="ghost" size="sm" onClick={clear}>
          <XIcon /> {t("clear")}
        </Button>
      )}
      {error && (
        <p className="text-destructive text-sm" role="alert">
          {error}
        </p>
      )}
    </div>
  )
}
