<?php

namespace Northlab\FilamentThemeManager\DTO;

use Spatie\LaravelData\Data;
use Northlab\FilamentThemeManager\Models\ThemeDeploymentLog;

class GitProcessData extends Data
{
    public function __construct(
        public string $repository,
        public string $branch,
        public string $vendor,
        public string $git_username,
        public ?string $git_password = null,
        public string $connection_type,
        public string $directory,
        public ThemeDeploymentLog $log,
    ) {}
}