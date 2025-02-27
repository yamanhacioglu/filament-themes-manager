<?php

namespace Northlab\FilamentThemeManager\Actions;


class CreateDeploymentLogAction
{
    public function run(\Northlab\FilamentThemeManager\DTO\DeploymentData $deploymentData) : \Northlab\FilamentThemeManager\Models\ThemeDeploymentLog
    {
        try{
            $theme_model = config('filament-theme-manager.theme_model', \Northlab\FilamentThemeManager\Models\Theme::class);

            $theme = $theme_model::where('id', $deploymentData->theme_id)->first();

            if(empty($theme)){
                throw new \Exception('Theme not found!');
            }

            if(!method_exists($theme, 'deployment_logs')){
                throw new \Exception("Failed to create {$theme->name} log : deployment relation is not set.");
            }

            return $theme->deployment_logs()->create(
                $deploymentData->toArray()
            );

        } catch(\Exception $e){
            \Illuminate\Support\Facades\Log::alert($e->getMessage());
            return null;
        }
    }
}