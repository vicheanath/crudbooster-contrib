/**
 * The URL if it is http(s), protocol-relative or a path on this site; undefined otherwise, so a
 * javascript: or data: URL in a prop can never become a link or an image source.
 */
export function safeUrl(url: unknown): string | undefined {
  if (typeof url !== "string" || url.trim() === "") return undefined
  try {
    const parsed = new URL(url, window.location.href)
    return parsed.protocol === "http:" || parsed.protocol === "https:" ? parsed.href : undefined
  } catch {
    return undefined
  }
}
