<form method="POST" action="{{ route('filament.students.auth.logout') }}" class="px-3 pb-3">
    @csrf
    <button type="submit" class="flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium text-white transition hover:bg-white/10" style="color: white">
        <x-filament::icon icon="heroicon-o-arrow-left-start-on-rectangle" class="h-5 w-5 text-white" style="color: white" />
        <span>{{ __('student-panel.navigation.logout') }}</span>
    </button>
</form>
