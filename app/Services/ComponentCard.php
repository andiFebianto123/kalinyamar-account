<?php
namespace App\Services;

use Illuminate\Support\Collection;

class ComponentCard
{
    public Collection $cards;

    public function __construct()
    {
        $this->cards = collect();
    }

    public function addCard(array $card): void
    {
        if (!isset($card['name'])) {
            throw new \InvalidArgumentException('Card must have a "name" key.');
        }

        $card['buttons'] = $card['buttons'] ?? [
            'left' => [],
            'right' => []
        ];

        $card['params'] = $card['params'] ?? [];
        $card['params']['name'] = $card['name'];

        $this->cards->put($card['name'], $card);
    }

    public function get(string $name): ?array
    {
        return $this->cards->get($name);
    }

    public function getCards(){
        return $this->cards;
    }

    public function remove(string $name): void
    {
        $this->cards->forget($name);
    }

    public function clear(): void
    {
        $this->cards = collect();
    }

    public static function renderHtmlAttributes(array $attributes): string
    {
        return collect($attributes)->map(function ($value, $key) {
            return $key . '="' . e($value) . '"';
        })->implode(' ');
    }
}
