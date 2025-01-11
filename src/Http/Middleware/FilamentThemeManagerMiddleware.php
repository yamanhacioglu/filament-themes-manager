<?php

namespace Northlab\FilamentThemeManager\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class FilamentThemeManagerMiddleware extends \Hexadog\ThemesManager\Http\Middleware\ThemeLoader
{

    public function handle(Request $request, Closure $next, $theme = null)
    {
        $theme_data = get_active_theme();

        $theme = get_theme_path();

        $is_apply_on_app = $theme_data?->meta['apply_on_app'] ?? false;
        $is_apply_on_admin = $theme_data?->meta['apply_on_admin'] ?? false;

        $filament_path = config('filament.path');



        if(!$is_apply_on_app){
            if(!str_contains($request->path(), "{$filament_path}/")){
                $theme = null;
            }
        }

        if(!$is_apply_on_admin){
            if(str_contains($request->path(), "{$filament_path}/")){
                $theme = null;
            }
        }

        // Call parent Middleware handle method
        return parent::handle($request, $next, $theme);
    }
}