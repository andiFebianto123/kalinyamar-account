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

        $card['buttons'] = $card['buttons'] ?? [
            'left' => [],
            'right' => []
        ];

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
