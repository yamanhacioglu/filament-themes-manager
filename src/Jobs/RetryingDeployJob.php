<?php

namespace Northlab\FilamentThemeManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Northlab\FilamentThemeManager\DTO\GetGitUrlData;
use Northlab\FilamentThemeManager\DTO\GitProcessData;
use Northlab\FilamentThemeManager\DTO\DeploymentData;
use Northlab\FilamentThemeManager\Models\ThemeDeploymentLog;
use Northlab\FilamentThemeManager\Jobs\Run\RunDeployJob;
use Northlab\FilamentThemeManager\Enum\GitConnectionEnum;
use Northlab\FilamentThemeManager\Enum\DeploymentTypeEnum;
use Northlab\FilamentThemeManager\Actions\NavigateGitUrlAction;
use Northlab\FilamentThemeManager\Actions\CreateDeploymentLogAction;

class RetryingDeployJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ThemeDeploymentLog $log
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
                'connection_type' => $this->log->theme->connection_type,
                'provider' => $this->log->theme->git_provider,
                'git_username' => $this->log->theme->git_username,
                'git_password' => $this->log->theme->meta['git_password'] ?? null
            ]);

            $repository = $navigateGitUrl->run($urlData);

            if (empty($repository)) {
                throw new \Exception("Repository URL invalid!");
            }

            $repository = "{$repository}{$this->log->theme->git_username}/{$this->log->theme->git_repository}.git";

            $logData = DeploymentData::from([
                'theme_id' => $this->log->theme->id,
                'name' => "Retry : Deploy {$this->log->theme->name}",
                'repository' => $repository,
                'branch' => $this->log->theme->git_branch,
                'git_username' => $this->log->theme->git_username,
                'connection_type' => $this->log->theme->connection_type,
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
                "branch" => $this->log->theme->git_branch,
                "vendor" => $this->log->theme->is_child
                    ? $this->log->theme->parent_theme->vendor
                    : $this->log->theme->vendor,
                "git_username" => $this->log->theme->git_username,
                "git_password" => $this->log->theme->connection_type === GitConnectionEnum::HTTPS()->value
                    ? $this->log->theme->meta['git_password']
                    : null,
                "connection_type" => $this->log->theme->connection_type,
                "directory" => $this->log->theme->directory,
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