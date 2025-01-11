<?php

namespace Northlab\FilamentThemeManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Northlab\FilamentThemeManager\Models\Theme;
use Northlab\FilamentThemeManager\DTO\GetGitUrlData;
use Northlab\FilamentThemeManager\DTO\GitProcessData;
use Northlab\FilamentThemeManager\DTO\DeploymentData;
use Northlab\FilamentThemeManager\Jobs\Run\RunDeployJob;
use Northlab\FilamentThemeManager\Enum\GitConnectionEnum;
use Northlab\FilamentThemeManager\Enum\DeploymentTypeEnum;
use Northlab\FilamentThemeManager\Actions\NavigateGitUrlAction;
use Northlab\FilamentThemeManager\Actions\CreateDeploymentLogAction;

class PreparingDeployJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Theme $theme
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        NavigateGitUrlAction $navigateGitUrl,
        CreateDeploymentLogAction $createDeploymentLog
    ): void {
        try {
            DB::beginTransaction();

            $urlData = GetGitUrlData::from([
                'connection_type' => $this->theme->connection_type,
                'provider' => $this->theme->git_provider,
                'git_username' => $this->theme->git_username,
                'git_password' => $this->theme->meta['git_password'] ?? null
            ]);

            $repository = $navigateGitUrl->run($urlData);

            if (empty($repository)) {
                throw new \Exception("Repository URL invalid!");
            }

            $repository = "{$repository}{$this->theme->git_username}/{$this->theme->git_repository}.git";

            $logData = DeploymentData::from([
                'theme_id' => $this->theme->id,
                'name' => "Deploy {$this->theme->name}",
                'repository' => $repository,
                'branch' => $this->theme->git_branch,
                'git_username' => $this->theme->git_username,
                'connection_type' => $this->theme->connection_type,
                'meta' => [
                    "type" => DeploymentTypeEnum::DEPLOY()->value
                ]
            ]);

            $log = $createDeploymentLog->run($logData);

            if (empty($log)) {
                throw new \Exception("Theme deployment log not found.");
            }

            $log = theme_deployment_log_writer($log, [
                'Resources are ready to be processed.',
                'Initializing deploy procedure ... '
            ]);

            $deployData = GitProcessData::from([
                "repository" => $repository,
                "branch" => $this->theme->git_branch,
                "vendor" => $this->theme->is_child ? $this->theme->parent_theme->vendor : $this->theme->vendor,
                "git_username" => $this->theme->git_username,
                "git_password" => $this->theme->connection_type === GitConnectionEnum::HTTPS()->value
                    ? $this->theme->meta['git_password']
                    : null,
                "connection_type" => $this->theme->connection_type,
                "directory" => $this->theme->directory,
                "log" => $log
            ]);

            RunDeployJob::dispatch($deployData);

            DB::commit();
        } catch(\Exception $e) {
            DB::rollBack();
            Log::alert($e->getMessage());
        }
    }
}