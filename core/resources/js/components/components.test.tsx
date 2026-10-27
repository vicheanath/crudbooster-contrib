import { act, render, screen, waitFor } from "@testing-library/react"
import userEvent from "@testing-library/user-event"
import { describe, expect, it, vi } from "vitest"
import { mount, register, registerMount } from "@/lib/registry"
import { resetLang, t } from "@/lib/lang"
import BulkActions from "./BulkActions"
import { confirmDialog, enableConfirmations } from "./ConfirmDialog"
import ThemeToggle from "./ThemeToggle"

describe("islands", () => {
  it("mounts registered components with their JSON props, once", async () => {
    document.body.innerHTML = `
      <div data-cb-component="Hello" data-cb-props='{"name":"Ada"}'>fallback</div>
      <div data-cb-component="Unknown">kept</div>`
    register("Hello", ({ name }: { name: string }) => <b>Hello {name}</b>)
    await act(async () => mount())
    await act(async () => mount())

    expect(document.body.innerHTML).toContain("<b>Hello Ada</b>")
    expect(document.body.innerHTML).not.toContain("fallback")
    expect(document.body.textContent).toContain("kept")
  })

  it("mounts plain functions registered with registerMount", () => {
    document.body.innerHTML = `<div data-cb-component="Plain" data-cb-props='{"n":2}'></div>`
    const mounter = vi.fn((element: HTMLElement, props: Record<string, unknown>) => {
      element.textContent = `n=${props.n}`
    })
    registerMount("Plain", mounter)

    expect(mounter).toHaveBeenCalledOnce()
    expect(document.body.textContent).toBe("n=2")
  })
})

describe("translations", () => {
  it("reads strings from the layout config and replaces placeholders", () => {
    document.body.innerHTML = `<script type="application/json" id="cb-panel-config">{"lang":{"selected":":count gewählt"}}</script>`
    resetLang()
    expect(t("selected", { count: 3 })).toBe("3 gewählt")
    expect(t("cancel")).toBe("Cancel")
    resetLang()
  })
})

describe("confirmations", () => {
  it("re-submits the button only after confirming", async () => {
    const user = userEvent.setup()
    document.body.innerHTML = `
      <form id="f" action="/x"><button type="submit" formaction="/delete" data-cb-confirm="Delete it?" data-cb-confirm-color="danger">Delete</button></form>`
    const onSubmit = vi.fn((event: Event) => event.preventDefault())
    document.getElementById("f")!.addEventListener("submit", onSubmit)
    enableConfirmations(document.getElementById("f")!)

    await user.click(screen.getByText("Delete"))
    expect(await screen.findByRole("alertdialog")).toHaveTextContent("Delete it?")
    expect(onSubmit).not.toHaveBeenCalled()

    await user.click(screen.getByRole("button", { name: "Cancel" }))
    await waitFor(() => expect(screen.queryByRole("alertdialog")).not.toBeInTheDocument())
    expect(onSubmit).not.toHaveBeenCalled()

    await user.click(screen.getByText("Delete"))
    await user.click(await screen.findByRole("button", { name: "Confirm" }))
    await waitFor(() => expect(onSubmit).toHaveBeenCalledOnce())
    expect((onSubmit.mock.calls[0][0] as SubmitEvent).submitter).toBe(screen.getByText("Delete"))
  })

  it("resolves false on Escape", async () => {
    const user = userEvent.setup()
    const answer = confirmDialog({ message: "Sure?" })
    await screen.findByRole("alertdialog")
    await user.keyboard("{Escape}")
    await expect(answer).resolves.toBe(false)
  })
})

describe("BulkActions", () => {
  it("counts ticked rows, drives select-all and submits the bulk form", async () => {
    const user = userEvent.setup()
    document.body.innerHTML = `
      <input type="checkbox" id="cb-select-all">
      <input type="checkbox" class="cb-select" value="1"><input type="checkbox" class="cb-select" value="2">
      <form id="cb-bulk-form" method="post"></form>
      <div id="bar"></div>`
    const submit = vi.spyOn(HTMLFormElement.prototype, "submit").mockImplementation(() => {})
    render(<BulkActions actions={[{ name: "archive", label: "Archive", url: "/bulk/archive" }]} />, { container: document.getElementById("bar")! })

    const toggle = screen.getByRole("button", { name: /Bulk actions/ })
    expect(toggle).toBeDisabled()

    await user.click(document.querySelector<HTMLInputElement>(".cb-select")!)
    expect(toggle).toHaveTextContent("1 selected")
    expect(document.querySelector<HTMLInputElement>("#cb-select-all")!.indeterminate).toBe(true)

    await user.click(document.getElementById("cb-select-all")!)
    expect(toggle).toHaveTextContent("2 selected")

    await user.click(toggle)
    await user.click(await screen.findByRole("menuitem", { name: "Archive" }))
    expect(document.getElementById("cb-bulk-form")!.getAttribute("action")).toBe("/bulk/archive")
    expect(submit).toHaveBeenCalledOnce()
    submit.mockRestore()
  })
})

describe("ThemeToggle", () => {
  it("switches the dark class and remembers the choice", async () => {
    const user = userEvent.setup()
    render(<ThemeToggle />)

    await user.click(screen.getByRole("button", { name: "Dark" }))
    expect(document.documentElement).toHaveClass("dark")
    expect(localStorage.getItem("cb-theme")).toBe("dark")

    await user.click(screen.getByRole("button", { name: "Light" }))
    expect(document.documentElement).not.toHaveClass("dark")
  })
})
