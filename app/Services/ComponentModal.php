<?php
namespace App\Services;

use Illuminate\Support\Collection;

class ComponentModal
{
    public Collection $modals;

    public function __construct()
    {
        $this->modals = collect();
    }

    public function addModal(array $modals): void
    {
        if (!isset($modals['name'])) {
            throw new \InvalidArgumentException('Card must have a "name" key.');
        }

        $modals['buttons'] = $modals['buttons'] ?? [
            'left' => [],
            'right' => []
        ];

        $modals['params'] = $modals['params'] ?? [];
        $modals['params']['name'] = $modals['name'];

        $modals['title_alignment'] = $modals['title_alignment'] ?? 'left';

        // Validate title alignment
        if (!in_array($modals['title_alignment'], ['left', 'center', 'right'])) {
            throw new \InvalidArgumentException('Invalid title_alignment value. Allowed: left, center, right.');
        }

        $this->modals->put($modals['name'], $modals);
    }

    public function get(string $name): ?array
    {
        return $this->modals->get($name);
    }

    public function getModals(){
        return $this->modals;
    }

    public function remove(string $name): void
    {
        $this->modals->forget($name);
    }

    public function clear(): void
    {
        $this->modals = collect();
    }

    public static function renderHtmlAttributes(array $attributes): string
    {
        return collect($attributes)->map(function ($value, $key) {
            return $key . '="' . e($value) . '"';
        })->implode(' ');
    }
}
