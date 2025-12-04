<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Data Transfer Object representing a competitor
 */
final readonly class CompetitorDTO
{
    public function __construct(
        public string $name,
        public ?string $productUrl,
        public ?float $price,
        public ?string $description,
        public ?string $positioning,
        public ?string $strengths,
        public ?string $notoriety,
        public array $socialMedia = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'product_url' => $this->productUrl,
            'price' => $this->price,
            'description' => $this->description,
            'positioning' => $this->positioning,
            'strengths' => $this->strengths,
            'notoriety' => $this->notoriety,
            'social_media' => $this->socialMedia,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? '',
            productUrl: $data['product_url'] ?? $data['url'] ?? null,
            price: isset($data['price']) ? (float) $data['price'] : null,
            description: $data['description'] ?? null,
            positioning: $data['positioning'] ?? null,
            strengths: $data['strengths'] ?? null,
            notoriety: $data['notoriety'] ?? null,
            socialMedia: $data['social_media'] ?? [],
        );
    }
}
