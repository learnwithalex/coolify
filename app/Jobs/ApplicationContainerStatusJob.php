<?php

namespace App\Jobs;

use App\Models\Application;
use App\Models\ApplicationPreview;
use App\Notifications\Notifications\Application\ApplicationStoppedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ApplicationContainerStatusJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $container_name;
    public string|null $pull_request_id;
    public Application $application;

    public function __construct($application, string $container_name, string|null $pull_request_id = null)
    {
        $this->application = $application;
        $this->container_name = $container_name;
        $this->pull_request_id = $pull_request_id;
    }
    public function uniqueId(): string
    {
        return $this->container_name;
    }
    public function handle(): void
    {
        ray('checking status of ' . $this->container_name);
        try {
            $status = get_container_status(server: $this->application->destination->server, container_id: $this->container_name, throwError: false);
            ray($this->application->status, $status);
            if ($this->application->status === 'running' && $status !== 'running') {
                $this->application->environment->project->team->notify(new ApplicationStoppedNotification($this->application));
            }

            if ($this->pull_request_id) {
                $preview = ApplicationPreview::findPreviewByApplicationAndPullId($this->application->id, $this->pull_request_id);
                $preview->status = $status;
                $preview->save();
            } else {
                $this->application->status = $status;
                $this->application->save();
            }
        } catch (\Exception $e) {
            ray($e->getMessage());
        }
    }
}