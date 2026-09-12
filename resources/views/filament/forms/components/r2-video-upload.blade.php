@php
    $parentStatePath = \Illuminate\Support\Str::beforeLast($getStatePath(), '.');
    $inputId = 'r2-video-'.md5($getStatePath());
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="courseVideoUploader({ baseUrl: @js($uploadBaseUrl), statePath: @js($parentStatePath), csrfToken: @js(csrf_token()) })" class="space-y-3">
        @if ($uploadBaseUrl)
            <input id="{{ $inputId }}" type="file" accept="video/mp4,video/quicktime,video/x-m4v,video/webm,video/x-matroska" x-on:change="selectFile($event)" x-bind:disabled="uploading" class="sr-only" />
            <label for="{{ $inputId }}" class="inline-flex cursor-pointer rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-500">{{ __('instructor.uploader.choose') }}</label>
            <div x-show="fileName" class="text-sm text-gray-600 dark:text-gray-300"><span x-text="fileName"></span> <span x-show="fileSize" x-text="`(${fileSize})`"></span></div>
            <button type="button" x-on:click="upload()" x-bind:disabled="! file || uploading" class="fi-btn fi-btn-size-md rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-50"><span x-show="! uploading">{{ __('instructor.uploader.upload') }}</span><span x-show="uploading" x-text="@js(__('instructor.uploader.uploading', ['progress' => '__PROGRESS__'])).replace('__PROGRESS__', progress)"></span></button>
            <div x-show="uploading || progress > 0" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50"><div class="mb-2 flex items-center justify-between gap-4"><span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="uploading ? 'Uploading video...' : 'Upload complete'"></span><span class="text-lg font-bold text-primary-600" x-text="`${progress}%`"></span></div><div class="h-3 overflow-hidden rounded-full bg-gray-200 shadow-inner dark:bg-gray-700"><div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-success-500 transition-all duration-300" x-bind:style="`width: ${progress}%`"></div></div></div>
            <p x-show="status" x-text="status" class="text-sm text-success-600"></p><p x-show="error" x-text="error" class="text-sm text-danger-600"></p>
        @else
            <p class="text-sm text-warning-600">{{ __('instructor.uploader.save_first') }}</p>
        @endif
    </div>
</x-dynamic-component>
