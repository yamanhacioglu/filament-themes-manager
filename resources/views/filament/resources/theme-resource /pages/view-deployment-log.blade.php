<x-filament-panels::page>
    <x-filament::section>
        <div class="space-y-6">
            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3 border-b py-3">
                    <div>
                        Process Started
                    </div>
                    <div>
                        {{ $deployment_log->created_at?->format('d/m/Y H:i:s') }}
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-b py-3">
                    <div>
                        Process End
                    </div>
                    <div>
                        {{ $deployment_log->process_end_at?->format('d/m/Y H:i:s') ?? "-" }}
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 border-b py-3">
                    <div>
                        Status
                    </div>
                    <div class="font-bold {{
                        match($deployment_log->status){
                            'pending' => 'text-warning-500',
                            'failed' => 'text-danger-500',
                            'successed' => 'text-success-500',
                            default => 'text-primary-500'
                        }
                    }}">
                        {{ ucwords($deployment_log->status) }}
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <div class="font-bold">
                    Output:
                </div>
                <div class="space-y-2 rounded-lg bg-primary-50 p-4 dark:bg-primary-950">
                    @foreach($deployment_log->meta['output'] as $output)
                        <div class="font-semibold text-primary-500 dark:text-primary-400">
                            {{ $output }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-panels::page>