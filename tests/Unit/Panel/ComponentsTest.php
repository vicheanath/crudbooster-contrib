<?php

namespace crocodicstudio\crudbooster\tests\Unit\Panel;

use crocodicstudio\crudbooster\tests\TestCase;
use CrudBooster\Actions\Action;
use CrudBooster\Forms\Fields\DatePicker;
use CrudBooster\Forms\Fields\FileUpload;
use CrudBooster\Forms\Fields\Select;
use CrudBooster\Forms\Fields\TextInput;
use CrudBooster\Forms\Form;
use CrudBooster\Forms\Section;
use CrudBooster\Navigation\NavigationItem;
use CrudBooster\Tables\Columns\BadgeColumn;
use CrudBooster\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class ComponentsTest extends TestCase
{
    private function record(array $attributes): Model
    {
        return (new class extends Model {})->forceFill($attributes);
    }

    public function test_labels_are_derived_from_names()
    {
        $this->assertSame('Category Name', TextColumn::make('category.name')->getLabel());
        $this->assertSame('Is Active', TextInput::make('is_active')->getLabel());
        $this->assertSame('Price', TextInput::make('price_cents')->label('Price')->getLabel());
    }

    public function test_text_columns_format_numbers_dates_and_limits()
    {
        $record = $this->record(['price' => 1234.5, 'born' => '2026-03-04 10:00:00', 'bio' => '<b>Long</b> biography']);

        $this->assertSame('$1,234.50', TextColumn::make('price')->money('$')->formatState(1234.5, $record));
        $this->assertSame('04/03/2026', TextColumn::make('born')->date('d/m/Y')->formatState('2026-03-04 10:00:00', $record));
        $this->assertSame('Long...', TextColumn::make('bio')->limit(4)->formatState('<b>Long</b> biography', $record));
        $this->assertSame('1.5 kg', TextColumn::make('weight')->suffix(' kg')->formatState(1.5, $record));
        $this->assertInstanceOf(HtmlString::class, TextColumn::make('bio')->html()->formatState('<b>x</b>', $record));
        $this->assertSame('B', TextColumn::make('bio')->formatStateUsing(fn ($state, $record) => strtoupper($state))->formatState('b', $record));
    }

    public function test_column_output_is_escaped_unless_marked_html()
    {
        $record = $this->record(['name' => '<script>x</script>']);

        $this->assertStringContainsString('&lt;script&gt;', TextColumn::make('name')->render($record));
        $this->assertStringContainsString('<span class="text-body-tertiary">', TextColumn::make('missing')->render($record));
        $this->assertStringContainsString('text-bg-danger', BadgeColumn::make('name')->colors(fn ($state) => 'danger')->render($record));
    }

    public function test_closures_receive_named_arguments_and_services()
    {
        $action = Action::make('archive')->visible(fn (Model $record, Request $request) => $record->status === 'open' && $request instanceof Request);

        $this->assertTrue($action->for(['record' => $this->record(['status' => 'open'])])->isVisible());
        $this->assertFalse($action->for(['record' => $this->record(['status' => 'closed'])])->isVisible());
        $this->assertFalse(Action::make('x')->hidden(fn (Model $record) => true)->for(['record' => $this->record([])])->isVisible());
    }

    public function test_password_inputs_hash_and_skip_empty_values_on_edit()
    {
        $model = $this->record([]);
        $field = TextInput::make('password')->password()->required()->bind($model, $this->record(['password' => 'hash']), 'edit');

        $this->assertFalse($field->isRequired(), 'An empty password keeps the current one');
        $this->assertNull($field->getState(), 'The stored hash is never shown');
        $this->assertFalse($field->shouldSave(''));
        $this->assertTrue(Hash::check('secret', $field->dehydrate('secret')));
        $this->assertTrue(TextInput::make('password')->password()->required()->bind($model, null, 'create')->isRequired());
    }

    public function test_type_rules_unique_and_options_validation()
    {
        $model = new class extends Model { protected $table = 'things'; };
        $rules = TextInput::make('email')->email()->maxLength(80)->unique()->bind($model, null, 'create')->getValidationRules();

        $this->assertSame('nullable', $rules['email'][0]);
        $this->assertSame('unique:things,email,NULL,id', (string) $rules['email'][1]);
        $this->assertSame(['email', 'max:80'], array_slice($rules['email'], 2));

        $select = Select::make('size')->options(['s', 'm'])->bind($model, null, 'create');
        $this->assertSame(['s' => 's', 'm' => 'm'], $select->getOptions());
        $this->assertSame('in:"s","m"', (string) $select->getValidationRules()['size'][1]);

        $multiple = Select::make('sizes')->options(['s' => 'Small'])->multiple()->bind($model, null, 'create');
        $this->assertSame(['sizes', 'sizes.*'], array_keys($multiple->getValidationRules()));
        $this->assertSame(['s'], $multiple->getValueFromRequest(new Request(['sizes' => ['s', '']])));
    }

    public function test_file_uploads_accept_only_allowed_types()
    {
        $model = $this->record([]);

        $this->assertSame(['nullable', 'file', 'image'], FileUpload::make('photo')->image()->bind($model, null, 'create')->getValidationRules()['photo']);
        $this->assertSame(['nullable', 'file', 'mimes:pdf', 'extensions:pdf', 'max:100'],
            FileUpload::make('cv')->acceptedFileTypes(['pdf'])->maxSize(100)->bind($model, null, 'create')->getValidationRules()['cv']);

        config(['crudbooster.UPLOAD_TYPES' => 'jpg, pdf']);
        $this->assertSame(['nullable', 'file', 'mimes:jpg,pdf', 'extensions:jpg,pdf'], FileUpload::make('doc')->bind($model, null, 'create')->getValidationRules()['doc']);

        // A real file: fakes report the type of their name, not of their content.
        $script = tempnam(sys_get_temp_dir(), 'cb');
        file_put_contents($script, "<?php echo 1;\n");
        $upload = new UploadedFile($script, 'doc.pdf', null, null, true);
        $validator = validator(['doc' => $upload], FileUpload::make('doc')->bind($model, null, 'create')->getValidationRules());
        $this->assertTrue($validator->fails(), 'A renamed script fails the content check');
        unlink($script);
    }

    public function test_forms_flatten_sections_and_honour_operations_and_visibility()
    {
        $form = (new Form)->schema([
            TextInput::make('name'),
            Section::make('More')->schema([
                TextInput::make('code')->onlyOn('create'),
                TextInput::make('secret')->visible(fn (?Model $record) => $record?->getAttribute('admin') === true),
                DatePicker::make('starts_at')->withTime()->dehydrateStateUsing(fn ($state) => $state.':00'),
            ]),
        ]);

        $form->bind($this->record([]), $this->record(['name' => 'Old', 'admin' => false, 'starts_at' => '2026-01-02 03:04:00']), 'edit');
        $names = array_map(fn ($field) => $field->getName(), $form->getFields());
        $this->assertSame(['name', 'starts_at'], $names);
        $this->assertSame('2026-01-02T03:04', $form->getFields()[1]->getState());

        [$attributes] = $form->getSubmittedState(new Request(['name' => 'New', 'code' => 'X', 'secret' => 'y', 'starts_at' => '2026-05-06T07:08']));
        $this->assertSame(['name' => 'New', 'starts_at' => '2026-05-06T07:08:00'], $attributes);
    }

    public function test_navigation_items_evaluate_badges_lazily()
    {
        $calls = 0;
        $item = NavigationItem::make('Orders')->url('/admin/orders')->badge(function () use (&$calls) {
            $calls++;

            return 3;
        });

        $this->assertSame(0, $calls);
        $this->assertSame(3, $item->getBadge());
        $this->assertSame('Orders', $item->getLabel());
    }
}
