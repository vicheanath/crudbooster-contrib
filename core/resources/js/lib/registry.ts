import { createElement, type ComponentType } from "react"
import { createRoot } from "react-dom/client"

/**
 * React "islands": Blade renders <div data-cb-component="Name" data-cb-props="{json}"> with a
 * plain HTML fallback inside, and mount() replaces it with the registered component. Components
 * registered after the page has loaded are mounted as soon as they are registered.
 */
// Islands receive JSON props from the server, so the registry cannot know their shape.
export type IslandComponent = ComponentType<any>
export type IslandMounter = (element: HTMLElement, props: Record<string, unknown>) => void | (() => void)

const components = new Map<string, IslandComponent>()
const mounters = new Map<string, IslandMounter>()
const roots = new WeakMap<HTMLElement, { unmount: () => void }>()

const loaded = () => typeof document !== "undefined" && document.readyState !== "loading"

export function register(name: string, component: IslandComponent): void {
  components.set(name, component)
  if (loaded()) mount(document, name)
}

/**
 * Registers a plain function instead of a React component, for scripts written without React:
 * mounter(element, props) receives the island element and its props, and may return a cleanup.
 */
export function registerMount(name: string, mounter: IslandMounter): void {
  mounters.set(name, mounter)
  if (loaded()) mount(document, name)
}

export function registered(name: string): boolean {
  return components.has(name) || mounters.has(name)
}

export function readProps(element: Element): Record<string, unknown> {
  const raw = element.getAttribute("data-cb-props")
  if (!raw) return {}
  try {
    return JSON.parse(raw)
  } catch (error) {
    console.error("CRUDBooster: invalid data-cb-props on", element, error)
    return {}
  }
}

/**
 * Mounts every island under root whose component is registered (only `only`, if given).
 */
export function mount(root: ParentNode = document, only: string | null = null): void {
  const selector = only ? `[data-cb-component="${CSS.escape(only)}"]` : "[data-cb-component]"
  root.querySelectorAll<HTMLElement>(selector).forEach((element) => {
    if (roots.has(element)) return
    const name = element.getAttribute("data-cb-component") ?? ""

    const mounter = mounters.get(name)
    if (mounter) {
      const cleanup = mounter(element, readProps(element))
      roots.set(element, { unmount: typeof cleanup === "function" ? cleanup : () => {} })
      element.setAttribute("data-cb-mounted", "")
      return
    }

    const component = components.get(name)
    if (!component) return

    const reactRoot = createRoot(element)
    roots.set(element, reactRoot)
    element.setAttribute("data-cb-mounted", "")
    reactRoot.render(createElement(component, readProps(element)))
  })
}

export function unmount(element: HTMLElement): void {
  roots.get(element)?.unmount()
  roots.delete(element)
  element.removeAttribute("data-cb-mounted")
}
