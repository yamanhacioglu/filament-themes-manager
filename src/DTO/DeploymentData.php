<?php

namespace Northlab\FilamentThemeManager\DTO;

use Spatie\LaravelData\Data;

class DeploymentData extends Data
{
    public function __construct(
        public int $theme_id,
        public ?int $parent_id,
        public string $name,
        public string $repository,
        public string $branch,
        public string $git_username,
        public string $connection_type,
        public ?string $status = 'pending',
        public ?string $commit = null,
        public ?array $meta = null,
    ) {}
}