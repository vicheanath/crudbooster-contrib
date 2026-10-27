import { useEffect, useState } from "react"
import { MonitorIcon, MoonIcon, SunIcon } from "lucide-react"
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group"
import { t } from "@/lib/lang"

const STORAGE_KEY = "cb-theme"
type Mode = "light" | "dark" | "system"
const MODES: { mode: Mode; label: "themeLight" | "themeDark" | "themeSystem"; Icon: typeof SunIcon }[] = [
  { mode: "light", label: "themeLight", Icon: SunIcon },
  { mode: "dark", label: "themeDark", Icon: MoonIcon },
  { mode: "system", label: "themeSystem", Icon: MonitorIcon },
]

function read(): Mode {
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    return stored === "light" || stored === "dark" ? stored : "system"
  } catch {
    return "system"
  }
}

/**
 * Applies a mode to <html>. The layout runs the same logic inline before the first paint.
 */
export function applyMode(mode: Mode): void {
  const dark = mode === "dark" || (mode === "system" && Boolean(window.matchMedia?.("(prefers-color-scheme: dark)").matches))
  if (document.documentElement.classList.contains("dark") === dark) return

  // Switch without animating every colour transition on the page (next-themes' disableTransitionOnChange).
  const pause = document.createElement("style")
  pause.textContent = "*,*::before,*::after{transition:none!important}"
  document.head.appendChild(pause)
  document.documentElement.classList.toggle("dark", dark)
  void window.getComputedStyle(document.body).opacity
  requestAnimationFrame(() => requestAnimationFrame(() => pause.remove()))
}

export default function ThemeToggle() {
  const [mode, setMode] = useState<Mode>(read)

  useEffect(() => {
    applyMode(mode)
    try {
      localStorage.setItem(STORAGE_KEY, mode)
    } catch {
      // Private mode: the choice lasts for this page only.
    }
    if (mode !== "system" || !window.matchMedia) return undefined
    const query = window.matchMedia("(prefers-color-scheme: dark)")
    const follow = () => applyMode("system")
    query.addEventListener?.("change", follow)
    return () => query.removeEventListener?.("change", follow)
  }, [mode])

  return (
    <ToggleGroup
      aria-label="Theme"
      value={[mode]}
      onValueChange={(value: readonly unknown[]) => value[0] && setMode(value[0] as Mode)}
      size="sm"
      spacing={0}
      variant="outline"
    >
      {MODES.map(({ mode: option, label, Icon }) => (
        <ToggleGroupItem key={option} value={option} aria-label={t(label)} title={t(label)}>
          <Icon />
        </ToggleGroupItem>
      ))}
    </ToggleGroup>
  )
}
