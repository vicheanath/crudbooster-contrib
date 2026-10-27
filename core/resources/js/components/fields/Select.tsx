import { useMemo, useState } from "react"
import {
  Combobox,
  ComboboxChip,
  ComboboxChips,
  ComboboxChipsInput,
  ComboboxContent,
  ComboboxEmpty,
  ComboboxInput,
  ComboboxItem,
  ComboboxList,
  ComboboxValue,
  useComboboxAnchor,
} from "@/components/ui/combobox"
import { t } from "@/lib/lang"
import type { FieldProps, Option } from "@/lib/types"

export interface SelectProps extends FieldProps {
  value?: string | string[]
  options?: Option[]
  multiple?: boolean
}

/**
 * A searchable combobox for one or several values. It submits hidden inputs named like a native
 * select: "name" for one value, "name[]" for several (or an empty "name" when none is chosen,
 * so clearing a many-to-many relationship is saved).
 */
export default function Select(props: SelectProps) {
  return props.multiple ? <MultipleSelect {...props} /> : <SingleSelect {...props} />
}

function useLabels(options: Option[]) {
  return useMemo(() => new Map(options.map((option) => [option.value, option.label])), [options])
}

function SingleSelect({ name, id, value, options = [], placeholder, required, disabled, invalid }: SelectProps) {
  const labels = useLabels(options)
  const initial = Array.isArray(value) ? value[0] : value
  const [selected, setSelected] = useState<string | null>(initial ? String(initial) : null)

  return (
    <>
      <input type="hidden" name={name} value={selected ?? ""} disabled={disabled} />
      <Combobox
        items={options.map((option) => option.value)}
        value={selected}
        onValueChange={(next) => setSelected(next ?? null)}
        itemToStringLabel={(item: string) => labels.get(item) ?? item}
        disabled={disabled}
        autoHighlight
      >
        <ComboboxInput
          id={id}
          placeholder={placeholder || t("selectOption")}
          aria-invalid={invalid || undefined}
          aria-required={required || undefined}
          disabled={disabled}
          showClear={!required && selected !== null}
          className="w-full"
        />
        <ComboboxContent>
          <ComboboxEmpty>{t("noResults")}</ComboboxEmpty>
          <ComboboxList>
            {(item: string) => (
              <ComboboxItem key={item} value={item}>
                {labels.get(item) ?? item}
              </ComboboxItem>
            )}
          </ComboboxList>
        </ComboboxContent>
      </Combobox>
    </>
  )
}

function MultipleSelect({ name, id, value, options = [], placeholder, disabled, invalid }: SelectProps) {
  const labels = useLabels(options)
  const anchor = useComboboxAnchor()
  const [selected, setSelected] = useState<string[]>(() => (Array.isArray(value) ? value.map(String) : []))

  return (
    <>
      {selected.length
        ? selected.map((item) => <input key={item} type="hidden" name={`${name}[]`} value={item} disabled={disabled} />)
        : <input type="hidden" name={name} value="" disabled={disabled} />}
      <Combobox
        multiple
        items={options.map((option) => option.value)}
        value={selected}
        onValueChange={(next) => setSelected(next)}
        itemToStringLabel={(item: string) => labels.get(item) ?? item}
        disabled={disabled}
        autoHighlight
      >
        <ComboboxChips ref={anchor} aria-invalid={invalid || undefined} className="w-full">
          <ComboboxValue>
            {(values: string[]) => (
              <>
                {values.map((item) => (
                  <ComboboxChip key={item} removeLabel={`${t("remove")} ${labels.get(item) ?? item}`}>
                    {labels.get(item) ?? item}
                  </ComboboxChip>
                ))}
                <ComboboxChipsInput id={id} placeholder={values.length ? undefined : placeholder || t("selectOption")} disabled={disabled} />
              </>
            )}
          </ComboboxValue>
        </ComboboxChips>
        <ComboboxContent anchor={anchor}>
          <ComboboxEmpty>{t("noResults")}</ComboboxEmpty>
          <ComboboxList>
            {(item: string) => (
              <ComboboxItem key={item} value={item}>
                {labels.get(item) ?? item}
              </ComboboxItem>
            )}
          </ComboboxList>
        </ComboboxContent>
      </Combobox>
    </>
  )
}
