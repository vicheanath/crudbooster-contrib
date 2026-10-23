<?php

namespace crocodicstudio\crudbooster\settings;

use crocodicstudio\crudbooster\contracts\Settings\SettingsRepositoryInterface;
use crocodicstudio\crudbooster\models\CmsSetting;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Collection;

/**
 * Reads cms_settings through the cache forever, under the "setting_{name}" keys CRUDBooster 5.x used,
 * so code that forgets those keys directly keeps working.
 */
final class SettingsRepository implements SettingsRepositoryInterface
{
    public function __construct(
        private readonly CmsSetting $settings,
        private readonly Cache $cache,
    ) {
    }

    public function get(string $name): mixed
    {
        $key = $this->key($name);
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $content = $this->settings->newQuery()->where('name', $name)->value('content');
        $this->cache->forever($key, $content);

        return $content;
    }

    public function set(string $name, mixed $content): void
    {
        $this->settings->newQuery()->where('name', $name)->update(['content' => $content]);
        $this->forget($name);
    }

    public function inGroup(string $group): Collection
    {
        return $this->settings->newQuery()->inGroup($group)->get();
    }

    public function groups(): Collection
    {
        return $this->settings->newQuery()->distinct()->orderBy('group_setting')->pluck('group_setting');
    }

    public function forget(string $name): void
    {
        $this->cache->forget($this->key($name));
    }

    private function key(string $name): string
    {
        return 'setting_'.$name;
    }
}
