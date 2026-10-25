import { createElement } from 'react'
import { createRoot } from 'react-dom/client'

/**
 * React "islands": Blade renders <div data-cb-component="Name" data-cb-props="{json}"> with a
 * plain HTML fallback inside, and mount() replaces it with the registered component. Components
 * registered after the page has loaded are mounted as soon as they are registered.
 */
const components = new Map()
const mounters = new Map()
const roots = new WeakMap()

export function register(name, component) {
  components.set(name, component)
  if (typeof document !== 'undefined' && document.readyState !== 'loading') {
    mount(document, name)
  }
}

/**
 * Registers a plain function instead of a React component, for scripts written without React:
 * mounter(element, props) receives the island element and its props, and may return a cleanup.
 */
export function registerMount(name, mounter) {
  mounters.set(name, mounter)
  if (typeof document !== 'undefined' && document.readyState !== 'loading') {
    mount(document, name)
  }
}

export function registered(name) {
  return components.has(name) || mounters.has(name)
}

export function readProps(element) {
  const raw = element.getAttribute('data-cb-props')
  if (!raw) return {}
  try {
    return JSON.parse(raw)
  } catch (error) {
    console.error('CRUDBooster: invalid data-cb-props on', element, error)
    return {}
  }
}

/**
 * Mounts every island under root whose component is registered (only `only`, if given).
 */
export function mount(root = document, only = null) {
  const selector = only ? `[data-cb-component="${only}"]` : '[data-cb-component]'
  root.querySelectorAll(selector).forEach((element) => {
    if (roots.has(element)) return
    const name = element.getAttribute('data-cb-component')

    if (mounters.has(name)) {
      const cleanup = mounters.get(name)(element, readProps(element))
      roots.set(element, { unmount: typeof cleanup === 'function' ? cleanup : () => {} })
      element.setAttribute('data-cb-mounted', '')
      return
    }

    const component = components.get(name)
    if (!component) return

    const reactRoot = createRoot(element)
    roots.set(element, reactRoot)
    element.setAttribute('data-cb-mounted', '')
    reactRoot.render(createElement(component, readProps(element)))
  })
}

export function unmount(element) {
  roots.get(element)?.unmount()
  roots.delete(element)
  element.removeAttribute('data-cb-mounted')
}
