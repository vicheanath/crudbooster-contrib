<?php

namespace crocodicstudio\crudbooster\forms;

use crocodicstudio\crudbooster\contracts\Forms\FieldHandlerInterface;
use crocodicstudio\crudbooster\forms\handlers\CheckboxFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\ChildFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\FileManagerFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\GoogleMapsFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\HeaderFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\MoneyFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\MultitextFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\Select2FieldHandler;
use crocodicstudio\crudbooster\forms\handlers\SelectFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\TextFieldHandler;
use crocodicstudio\crudbooster\forms\handlers\UploadFieldHandler;
use Illuminate\Contracts\Container\Container;

/**
 * Maps form field types to their handlers. Apps add or replace types without touching CBController:
 *
 *     app(FieldHandlerRegistry::class)->register('signature', SignatureFieldHandler::class);
 */
class FieldHandlerRegistry
{
    public const DEFAULT_HANDLERS = [
        'checkbox' => CheckboxFieldHandler::class,
        'child' => ChildFieldHandler::class,
        'filemanager' => FileManagerFieldHandler::class,
        'googlemaps' => GoogleMapsFieldHandler::class,
        'header' => HeaderFieldHandler::class,
        'money' => MoneyFieldHandler::class,
        'multitext' => MultitextFieldHandler::class,
        'select' => SelectFieldHandler::class,
        'select2' => Select2FieldHandler::class,
        'upload' => UploadFieldHandler::class,
    ];

    /** @var array<string, FieldHandlerInterface|class-string<FieldHandlerInterface>> */
    private array $handlers = [];

    /** @var array<int, FieldHandlerInterface|class-string<FieldHandlerInterface>> */
    private array $matchers = [];

    private FieldHandlerInterface|string $fallback = TextFieldHandler::class;

    public function __construct(private readonly Container $container)
    {
        foreach (self::DEFAULT_HANDLERS as $type => $handler) {
            $this->register($type, $handler);
        }
    }

    /**
     * Handles one field type. A class name is resolved from the container when first used.
     */
    public function register(string $type, FieldHandlerInterface|string $handler): static
    {
        $this->handlers[$type] = $handler;

        return $this;
    }

    /**
     * Adds a handler that picks its types through supports(), checked after registered types.
     */
    public function add(FieldHandlerInterface|string $handler): static
    {
        $this->matchers[] = $handler;

        return $this;
    }

    /**
     * The handler for types nothing else claims (text, textarea, date, password, ...).
     */
    public function fallback(FieldHandlerInterface|string $handler): static
    {
        $this->fallback = $handler;

        return $this;
    }

    public function has(string $type): bool
    {
        return isset($this->handlers[$type]);
    }

    public function resolve(?string $type): FieldHandlerInterface
    {
        $type = (string) $type;

        if (isset($this->handlers[$type])) {
            return $this->handlers[$type] = $this->instance($this->handlers[$type]);
        }

        foreach ($this->matchers as $i => $handler) {
            $handler = $this->matchers[$i] = $this->instance($handler);
            if ($handler->supports($type)) {
                return $handler;
            }
        }

        return $this->fallback = $this->instance($this->fallback);
    }

    private function instance(FieldHandlerInterface|string $handler): FieldHandlerInterface
    {
        return $handler instanceof FieldHandlerInterface ? $handler : $this->container->make($handler);
    }
}
