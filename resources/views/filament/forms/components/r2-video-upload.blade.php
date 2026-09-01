@php
    $parentStatePath = \Illuminate\Support\Str::beforeLast($getStatePath(), '.');
    $inputId = 'r2-video-'.md5($getStatePath());
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="courseVideoUploader({ baseUrl: @js($uploadBaseUrl), statePath: @js($parentStatePath), csrfToken: @js(csrf_token()) })" class="space-y-3">
        @if ($uploadBaseUrl)
            <input id="{{ $inputId }}" type="file" accept="video/mp4,video/quicktime,video/x-m4v,video/webm,video/x-matroska" x-on:change="selectFile($event)" x-bind:disabled="uploading" class="sr-only" />
            <label for="{{ $inputId }}" class="inline-flex cursor-pointer rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-500">Choose video file</label>
            <div x-show="fileName" class="text-sm text-gray-600 dark:text-gray-300"><span x-text="fileName"></span> <span x-show="fileSize" x-text="`(${fileSize})`"></span></div>
            <button type="button" x-on:click="upload()" x-bind:disabled="! file || uploading" class="fi-btn fi-btn-size-md rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-50"><span x-show="! uploading">Upload video to R2</span><span x-show="uploading" x-text="`Uploading ${progress}%`"></span></button>
            <div x-show="uploading || progress > 0" class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50"><div class="mb-2 flex items-center justify-between gap-4"><span class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="uploading ? 'Uploading video...' : 'Upload complete'"></span><span class="text-lg font-bold text-primary-600" x-text="`${progress}%`"></span></div><div class="h-3 overflow-hidden rounded-full bg-gray-200 shadow-inner dark:bg-gray-700"><div class="h-full rounded-full bg-gradient-to-r from-primary-500 to-success-500 transition-all duration-300" x-bind:style="`width: ${progress}%`"></div></div></div>
            <p x-show="status" x-text="status" class="text-sm text-success-600"></p><p x-show="error" x-text="error" class="text-sm text-danger-600"></p>
        @else
            <p class="text-sm text-warning-600">Save the course first, then edit it to upload long videos directly to R2.</p>
        @endif
    </div>
</x-dynamic-component>

@if (false)
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('courseVideoUploader', ({ baseUrl, statePath, csrfToken }) => ({
        baseUrl, statePath, csrfToken, file: null, fileName: '', fileSize: '', uploading: false, progress: 0, status: '', error: '', partProgress: {},
        selectFile(event) { this.file = event.target.files?.[0] ?? null; this.fileName = this.file?.name ?? ''; this.fileSize = this.file ? this.formatBytes(this.file.size) : ''; this.progress = 0; this.status = ''; this.error = ''; },
        async upload() { if (! this.file || ! this.baseUrl || this.uploading) return; this.uploading = true; this.progress = 0; this.status = ''; this.error = ''; this.partProgress = {}; let upload = null; try { upload = await this.request('/initiate', { file_name: this.file.name, file_size: this.file.size, content_type: this.file.type || 'video/mp4' }); const parts = await this.uploadParts(upload); const result = await this.request('/complete', { key: upload.key, upload_id: upload.upload_id, parts }); this.$wire.set(`${this.statePath}.url`, result.key, false); this.progress = 100; this.status = 'Video uploaded. Its duration will be calculated after saving the course.'; } catch (error) { if (upload?.upload_id) await this.abort(upload).catch(() => {}); this.error = error.message || 'The video upload failed.'; } finally { this.uploading = false; } },
        async uploadParts(upload) { const cursor = { value: 1 }, completed = []; const workers = Array.from({ length: Math.min(4, upload.parts_count) }, async () => { while (cursor.value <= upload.parts_count) { const partNumber = cursor.value++; const start = (partNumber - 1) * upload.part_size; const signed = await this.request('/sign-part', { key: upload.key, upload_id: upload.upload_id, part_number: partNumber }); completed.push({ part_number: partNumber, etag: await this.putPart(signed.upload_url, this.file.slice(start, Math.min(start + upload.part_size, this.file.size)), partNumber) }); } }); await Promise.all(workers); return completed.sort((left, right) => left.part_number - right.part_number); },
        putPart(url, blob, partNumber) { return new Promise((resolve, reject) => { const xhr = new XMLHttpRequest(); xhr.open('PUT', url); xhr.addEventListener('load', () => { if (xhr.status < 200 || xhr.status >= 300) return reject(new Error(`R2 rejected part ${partNumber}.`)); const etag = xhr.getResponseHeader('ETag'); etag ? resolve(etag) : reject(new Error('R2 CORS must expose the ETag header.')); }); xhr.addEventListener('error', () => reject(new Error(`Part ${partNumber} could not be uploaded.`))); xhr.send(blob); }); },
        abort(upload) { return this.request('/abort', { key: upload.key, upload_id: upload.upload_id }, 'DELETE'); },
        async request(path, body, method = 'POST') { const response = await fetch(`${this.baseUrl}${path}`, { method, credentials: 'same-origin', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken }, body: JSON.stringify(body) }); if (response.status === 204) return null; const data = await response.json().catch(() => ({})); if (! response.ok) throw new Error(data.errors ? Object.values(data.errors).flat()[0] : data.message || 'The upload request failed.'); return data; },
        formatBytes(bytes) { if (! bytes) return '0 B'; const units = ['B', 'KB', 'MB', 'GB', 'TB']; const unit = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1); return `${(bytes / (1024 ** unit)).toFixed(unit ? 2 : 0)} ${units[unit]}`; },
    }));
});
</script>
@endif
