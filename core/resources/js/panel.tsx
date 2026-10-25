import "@fontsource-variable/geist"
import "../css/panel.css"
import React from "react"
import { mount, register, registerMount, registered, unmount } from "@/lib/registry"
import { confirmDialog, enableConfirmations } from "@/components/ConfirmDialog"
import BulkActions from "@/components/BulkActions"
import ThemeToggle from "@/components/ThemeToggle"
import CheckboxList from "@/components/fields/CheckboxList"
import DatePicker from "@/components/fields/DatePicker"
import FileUpload from "@/components/fields/FileUpload"
import Radio from "@/components/fields/Radio"
import Select from "@/components/fields/Select"
import Textarea from "@/components/fields/Textarea"
import TextInput from "@/components/fields/TextInput"
import Toggle from "@/components/fields/Toggle"

const builtIn = { TextInput, Textarea, Select, Radio, CheckboxList, Toggle, DatePicker, FileUpload, BulkActions, ThemeToggle }
Object.entries(builtIn).forEach(([name, component]) => register(name, component))

/**
 * The page-wide behaviour that needs no component: the mobile sidebar and dismissible alerts.
 */
function enableLayout(): void {
  document.addEventListener("click", (event) => {
    const target = event.target as Element
    if (target.closest("[data-cb-sidebar-toggle]")) {
      document.documentElement.toggleAttribute("data-cb-sidebar-open")
    } else if (target.closest("[data-cb-sidebar-close]")) {
      document.documentElement.removeAttribute("data-cb-sidebar-open")
    }
    target.closest("[data-cb-dismiss]")?.closest("[data-cb-dismissible]")?.remove()
  })
}

/**
 * For your own scripts: CrudBooster.register("ColorWheel", ColorWheel) mounts every
 * <div data-cb-component="ColorWheel">, and CrudBooster.mount(element) mounts islands added later.
 * Components must use this page's React (CrudBooster.React), or register a plain function with
 * CrudBooster.registerMount(name, (element, props) => cleanup). A script that runs before this
 * one can wait for the "crudbooster:ready" event on document.
 */
const api = { React, register, registerMount, registered, mount, unmount, confirm: confirmDialog }

declare global {
  interface Window {
    CrudBooster: typeof api
  }
}

window.CrudBooster = Object.assign(window.CrudBooster || {}, api)

function boot(): void {
  enableLayout()
  enableConfirmations()
  mount()
  document.dispatchEvent(new CustomEvent("crudbooster:ready", { detail: window.CrudBooster }))
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", boot, { once: true })
} else {
  boot()
}
