<?php

namespace Northlab\FilamentThemeManager\Actions;

use Northlab\FilamentThemeManager\DTO\GetGitUrlData;
use Northlab\FilamentThemeManager\Enum\GitProviderEnum;
use Northlab\FilamentThemeManager\Enum\GitConnectionEnum;


class NavigateGitUrlAction
{
    public function run(GetGitUrlData $urlDTO) : ?string
    {
        return match($urlDTO->connection_type){
            GitConnectionEnum::HTTPS()->value => match($urlDTO->provider){
                GitProviderEnum::GITHUB()->value => "https://github.com/",
                GitProviderEnum::GITLAB()->value => "https://gitlab.com/",
                default => null
            },
            GitConnectionEnum::SSH()->value => match($urlDTO->provider){
                GitProviderEnum::GITHUB()->value => "git@github.com:",
                GitProviderEnum::GITLAB()->value => "git@gitlab.com:",
                default => null
            },
            default => null
        };
    }
}