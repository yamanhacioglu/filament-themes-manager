<?php

namespace Northlab\FilamentThemeManager\DTO;

use Spatie\LaravelData\Data;

class GetGitUrlData extends Data
{
    public function __construct(
        public string $connection_type,
        public string $git_username,
        public ?string $git_password = null,
        public string $provider,
    ) {}
}