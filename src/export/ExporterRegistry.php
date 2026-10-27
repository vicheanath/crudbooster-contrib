<?php

namespace crocodicstudio\crudbooster\export;

use crocodicstudio\crudbooster\contracts\Export\ExporterInterface;
use Illuminate\Contracts\Container\Container;

/**
 * The formats offered by the index page's export dialog. Apps add their own:
 *
 *     app(ExporterRegistry::class)->register('json', JsonExporter::class);
 *
 * @deprecated 7.2 Only used by CBController modules. Export from a resource with a BulkAction that returns a
 *     download. See docs/en/deprecations.md.
 */
class ExporterRegistry
{
    /** @var array<string, ExporterInterface|class-string<ExporterInterface>> */
    private array $exporters = [
        'pdf' => PdfExporter::class,
        'xls' => ExcelExporter::class,
        'csv' => CsvExporter::class,
    ];

    public function __construct(private readonly Container $container)
    {
    }

    public function register(string $format, ExporterInterface|string $exporter): static
    {
        $this->exporters[$format] = $exporter;

        return $this;
    }

    public function forget(string $format): static
    {
        unset($this->exporters[$format]);

        return $this;
    }

    public function has(string $format): bool
    {
        return isset($this->exporters[$format]);
    }

    public function get(string $format): ?ExporterInterface
    {
        if (! $this->has($format)) {
            return null;
        }

        $exporter = $this->exporters[$format];

        return $this->exporters[$format] = $exporter instanceof ExporterInterface ? $exporter : $this->container->make($exporter);
    }

    /**
     * @return array<string, string> format => label
     */
    public function labels(): array
    {
        $labels = [];
        foreach (array_keys($this->exporters) as $format) {
            $labels[$format] = $this->get($format)->label();
        }

        return $labels;
    }
}
