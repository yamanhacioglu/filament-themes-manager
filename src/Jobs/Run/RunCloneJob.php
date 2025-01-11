<?php

namespace Northlab\FilamentThemeManager\Jobs\Run;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Northlab\FilamentThemeManager\Enum\DeploymentStatusEnum;

class RunCloneJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public \Northlab\FilamentThemeManager\DTO\GitProcessData $gitCloneDTO
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try{
            $theme_directory = theme_directory();

            $this->gitCloneDTO->log->status = DeploymentStatusEnum::PROCESSING()->value;

            $this->gitCloneDTO->log->save();

            if(file_exists($theme_directory)){
                if(!is_dir($theme_directory)){
                    throw new \Exception("Directory {$theme_directory} has already registered, but it's not a directory.");
                }
            }

            if(!file_exists($theme_directory)){
                mkdir($theme_directory);
            }

            $vendor_directory = "{$theme_directory}/{$this->gitCloneDTO->vendor}";
            $full_theme_directory = "{$vendor_directory}/{$this->gitCloneDTO->directory}";


            if(file_exists($vendor_directory)){
                if(!is_dir($vendor_directory)){
                    throw new \Exception("Directory {$vendor_directory} has already registered, but it's not a directory.");
                }
            }

            if(!file_exists($vendor_directory)){
                mkdir($vendor_directory);
            }

            if(file_exists($full_theme_directory)){
                throw new \Exception("Directory {$full_theme_directory} has been used by other theme.");
            }


            $cloneProcess = new Process([
                'git',
                'clone',
                '--single-branch',
                '--branch',
                $this->gitCloneDTO->branch,
                $this->gitCloneDTO->repository,
                $this->gitCloneDTO->directory
            ]);
            $cloneProcess->setWorkingDirectory($vendor_directory);
            $cloneProcess->run();
            $cloneOutput = [];

            foreach ($cloneProcess as $type => $data) {
                $cloneOutput[] = $data;
            }

            theme_deployment_log_writer($this->gitCloneDTO->log, $cloneOutput);

            if($cloneProcess->isSuccessful()){
                $getCurrentCommit = new Process([
                    'git',
                    'rev-parse',
                    'HEAD'
                ]);
                $getCurrentCommit->setWorkingDirectory($full_theme_directory);
                $getCurrentCommit->run();

                $currentCommit = null;

                foreach ($getCurrentCommit as $type => $data) {
                    if(empty($currentCommit)){
                        $currentCommit = $data;
                    }
                }

                $this->gitCloneDTO->log->commit = $currentCommit;
                $this->gitCloneDTO->log->status = DeploymentStatusEnum::SUCCESSED()->value;
                $this->gitCloneDTO->log->process_end_at = now();
                $this->gitCloneDTO->log->save();

                theme_deployment_log_writer($this->gitCloneDTO->log, [
                    "Clone from repository {$this->gitCloneDTO->repository} successed!"
                ]);

                return;
            }

            $this->gitCloneDTO->log->status = DeploymentStatusEnum::FAILED()->value;
            $this->gitCloneDTO->log->process_end_at = now();

            $this->gitCloneDTO->log->save();

            theme_deployment_log_writer($this->gitCloneDTO->log, [
                "Clone from repository {$this->gitCloneDTO->repository} failed!",
                'Unexpected Error.'
            ]);

        } catch(\Exception $e){
            \Illuminate\Support\Facades\Log::alert($e->getMessage());
            theme_deployment_log_writer($this->gitCloneDTO->log, [
                $e->getMessage()
            ]);
            $this->gitCloneDTO->log->status = DeploymentStatusEnum::FAILED()->value;
            $this->gitCloneDTO->log->process_end_at = now();

            $this->gitCloneDTO->log->save();
        }
    }
}