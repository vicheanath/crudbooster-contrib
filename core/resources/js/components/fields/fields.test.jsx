import { act, fireEvent, render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { describe, expect, it } from 'vitest'
import CheckboxList from './CheckboxList'
import FileUpload from './FileUpload'
import Select from './Select'
import TextInput from './TextInput'
import Toggle from './Toggle'

const options = [
  { value: '1', label: 'Tools' },
  { value: '2', label: 'Toys' },
  { value: '10', label: 'Garden' },
]

/** What the form would submit, as [name, value] pairs. */
function submitted(container) {
  const form = document.createElement('form')
  form.appendChild(container.cloneNode(true))
  return [...new FormData(form).entries()].map(([name, value]) => [name, typeof value === 'string' ? value : value.name])
}

describe('Select', () => {
  it('submits the chosen value under the field name', async () => {
    const user = userEvent.setup()
    const { container } = render(<Select name="category_id" id="f" options={options} value="1" />)

    expect(submitted(container)).toEqual([['category_id', '1']])
    expect(screen.getByRole('combobox')).toHaveTextContent('Tools')

    await user.click(screen.getByRole('combobox'))
    await user.type(screen.getByRole('searchbox'), 'gar')
    expect(screen.getAllByRole('option')).toHaveLength(1)
    await user.keyboard('{Enter}')

    expect(submitted(container)).toEqual([['category_id', '10']])
    expect(screen.queryByRole('listbox')).not.toBeInTheDocument()
  })

  it('can be cleared when it is optional, not when required', async () => {
    const user = userEvent.setup()
    const { container, rerender } = render(<Select name="s" id="f" options={options} value="2" />)
    await user.click(screen.getByLabelText('Clear'))
    expect(submitted(container)).toEqual([['s', '']])

    rerender(<Select name="r" id="g" options={options} value="2" required />)
    expect(screen.queryByLabelText('Clear')).not.toBeInTheDocument()
  })

  it('submits name[] per value when multiple, and an empty name when none', async () => {
    const user = userEvent.setup()
    const { container } = render(<Select name="tags" id="f" options={options} value={['2']} multiple />)
    expect(submitted(container)).toEqual([['tags[]', '2']])

    await user.click(screen.getByRole('combobox'))
    await user.click(screen.getByRole('option', { name: 'Garden' }))
    expect(submitted(container)).toEqual([['tags[]', '2'], ['tags[]', '10']])
    expect(screen.getByRole('listbox')).toBeInTheDocument() // stays open for more picks

    await user.click(screen.getByLabelText('Remove Toys'))
    await user.click(screen.getByLabelText('Remove Garden'))
    expect(submitted(container)).toEqual([['tags', '']])
  })

  it('navigates with the keyboard and closes on Escape', async () => {
    const user = userEvent.setup()
    const { container } = render(<Select name="s" id="f" options={options} />)

    screen.getByRole('combobox').focus()
    await user.keyboard('{ArrowDown}')
    expect(screen.getByRole('listbox')).toBeInTheDocument()
    await user.keyboard('{ArrowDown}{Enter}')
    expect(submitted(container)).toEqual([['s', '2']])

    await user.click(screen.getByRole('combobox'))
    await user.keyboard('{Escape}')
    expect(screen.queryByRole('listbox')).not.toBeInTheDocument()
  })

  it('shows no results for an unmatched search', async () => {
    const user = userEvent.setup()
    render(<Select name="s" id="f" options={options} />)
    await user.click(screen.getByRole('combobox'))
    await user.type(screen.getByRole('searchbox'), 'zzz')
    expect(screen.getByText('No results')).toBeInTheDocument()
  })
})

describe('Toggle', () => {
  it('submits 1 or 0', async () => {
    const user = userEvent.setup()
    const { container } = render(<Toggle name="is_active" id="t" label="Active" value />)
    expect(submitted(container)).toEqual([['is_active', '1']])

    await user.click(screen.getByLabelText('Active'))
    expect(screen.getByRole('switch')).toHaveAttribute('aria-checked', 'false')
    expect(submitted(container)).toEqual([['is_active', '0']])
  })
})

describe('CheckboxList', () => {
  it('submits the ticked values, or an empty value when none', async () => {
    const user = userEvent.setup()
    const { container } = render(<CheckboxList name="sizes" id="c" options={options} value={['1']} />)
    expect(submitted(container)).toEqual([['sizes', ''], ['sizes[]', '1']])

    await user.click(screen.getByLabelText('Tools'))
    expect(submitted(container)).toEqual([['sizes', '']])
  })
})

describe('TextInput', () => {
  it('shows and hides a password, and never prefills it', async () => {
    const user = userEvent.setup()
    const { container } = render(<TextInput name="password" id="p" type="password" />)
    const input = container.querySelector('input')
    expect(input).toHaveAttribute('type', 'password')
    expect(input).toHaveAttribute('autocomplete', 'new-password')

    await user.click(screen.getByLabelText('Show password'))
    expect(input).toHaveAttribute('type', 'text')
  })

  it('renders prefixes and passes extra attributes with React names', () => {
    const { container } = render(<TextInput name="price" id="x" value="9.5" prefix="$" attributes={{ autocomplete: 'off', 'data-mask': '99' }} />)
    const input = container.querySelector('input')
    expect(screen.getByText('$')).toBeInTheDocument()
    expect(input).toHaveValue('9.5')
    expect(input).toHaveAttribute('autocomplete', 'off')
    expect(input).toHaveAttribute('data-mask', '99')
  })
})

describe('FileUpload', () => {
  it('marks the current file for removal, and can undo', async () => {
    const user = userEvent.setup()
    const { container } = render(
      <FileUpload name="photo" id="u" image fileUrl="/storage/a.jpg" fileName="a.jpg" removeName="photo__remove" />,
    )
    expect(submitted(container)).toEqual([['photo', '']])

    await user.click(screen.getByRole('button', { name: 'Remove' }))
    expect(screen.getByText('Will be removed when you save.')).toBeInTheDocument()
    expect(submitted(container)).toContainEqual(['photo__remove', '1'])

    await user.click(screen.getByRole('button', { name: 'Undo' }))
    expect(submitted(container)).not.toContainEqual(['photo__remove', '1'])
  })

  it('rejects files over the size limit before upload', async () => {
    const { container } = render(<FileUpload name="doc" id="d" maxSize={1} />)
    const big = new File(['x'.repeat(2048)], 'big.pdf', { type: 'application/pdf' })
    await act(async () => {
      fireEvent.change(container.querySelector('input[type=file]'), { target: { files: [big] } })
    })
    expect(screen.getByRole('alert')).toHaveTextContent('larger than 1 KB')
  })
})
