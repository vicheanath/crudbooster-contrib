import '../css/panel.css'
import React from 'react'
import { mount, register, registerMount, registered, unmount } from './lib/registry'
import { enableConfirmations, confirmDialog } from './components/ConfirmDialog'
import BulkActions from './components/BulkActions'
import ThemeToggle from './components/ThemeToggle'
import CheckboxList from './components/fields/CheckboxList'
import DatePicker from './components/fields/DatePicker'
import FileUpload from './components/fields/FileUpload'
import Radio from './components/fields/Radio'
import Select from './components/fields/Select'
import Textarea from './components/fields/Textarea'
import TextInput from './components/fields/TextInput'
import Toggle from './components/fields/Toggle'

const builtIn = { TextInput, Textarea, Select, Radio, CheckboxList, Toggle, DatePicker, FileUpload, BulkActions, ThemeToggle }
Object.entries(builtIn).forEach(([name, component]) => register(name, component))

/**
 * The page-wide behaviour that needs no component: the mobile sidebar and dismissible alerts.
 */
function enableLayout() {
  document.addEventListener('click', (event) => {
    if (event.target.closest('[data-cb-sidebar-toggle]')) {
      document.documentElement.toggleAttribute('data-cb-sidebar-open')
    } else if (event.target.closest('[data-cb-sidebar-close]')) {
      document.documentElement.removeAttribute('data-cb-sidebar-open')
    }
    const dismiss = event.target.closest('[data-cb-dismiss]')
    if (dismiss) dismiss.closest('[data-cb-dismissible]')?.remove()
  })
}

function boot() {
  enableLayout()
  enableConfirmations()
  mount()
  document.dispatchEvent(new CustomEvent('crudbooster:ready', { detail: window.CrudBooster }))
}

/**
 * For your own scripts: CrudBooster.register('ColorWheel', ColorWheel) mounts every
 * <div data-cb-component="ColorWheel">, and CrudBooster.mount(element) mounts islands added later.
 * Components must use this page's React (CrudBooster.React), or register a plain function with
 * CrudBooster.registerMount(name, (element, props) => cleanup). A script that runs before this
 * one can wait for the "crudbooster:ready" event on document.
 */
window.CrudBooster = Object.assign(window.CrudBooster || {}, {
  React, register, registerMount, registered, mount, unmount, confirm: confirmDialog,
})

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true })
} else {
  boot()
}
