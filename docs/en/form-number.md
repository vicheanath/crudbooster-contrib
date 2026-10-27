---
deprecated:
  instead: "`TextInput::make('stock')->numeric()`."
  migrate: /en/migration-examples#text-email-number-password-textarea
  guide: /en/panel#forms
---

# Number Form Type
Showing input number

### Code Sample
```php
$this->form[] = ['label'=>'Amount','name'=>'amount','type'=>'number','min'=>0,'max'=>100];
```
By putting `min` attribute, it will make a minimal amount of the number
and also by putting `max` attribute, it will make a maximal amount of the number

## What's Next
- [Form Input Type: password](./form-password.md)

## Table Of Contents
- [Back To Index](./index.md)