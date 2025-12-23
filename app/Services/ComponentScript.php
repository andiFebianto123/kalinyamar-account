<?php
namespace App\Services;

use Illuminate\Support\Collection;

class ComponentScript
{
    protected Collection $scripts;

    public function __construct()
    {
        $this->scripts = collect();
    }

    public function addScript(array $script): void
    {
        if (!isset($script['name'])) {
            throw new \InvalidArgumentException('Script must have a "name".');
        }

        $this->scripts->put($script['name'], [
            'line' => $script['line'] ?? 'bottom',
            'type' => $script['type'] ?? 'inline',
            'content' => $script['content'] ?? '',
            'defer' => $script['defer'] ?? false,
            'async' => $script['async'] ?? false,
        ]);
    }

    public function getScripts(): Collection
    {
        return $this->scripts;
    }

    public function remove(string $name): void
    {
        $this->scripts->forget($name);
    }
}
