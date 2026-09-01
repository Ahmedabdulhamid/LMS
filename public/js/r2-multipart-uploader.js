(() => {
    const uploader = ({ baseUrl, statePath, csrfToken, kind }) => ({
        baseUrl, statePath, csrfToken, kind,
        file: null, fileName: '', fileSize: '', uploading: false,
        progress: 0, status: '', error: '', partProgress: {},
        uploadedBytes: 0, uploadSpeed: 0, startedAt: null,

        selectFile(event) {
            this.file = event.target.files?.[0] ?? null;
            this.fileName = this.file?.name ?? '';
            this.fileSize = this.file ? this.formatBytes(this.file.size) : '';
            this.progress = 0;
            this.uploadedBytes = 0;
            this.uploadSpeed = 0;
            this.status = '';
            this.error = '';
        },

        async upload() {
            if (! this.file || ! this.baseUrl || this.uploading) return;
            this.uploading = true;
            this.progress = 0;
            this.status = '';
            this.error = '';
            this.partProgress = {};
            this.uploadedBytes = 0;
            this.uploadSpeed = 0;
            this.startedAt = Date.now();
            let upload = null;

            try {
                const initiatePath = this.kind === 'attachment' ? '/initiate-attachment' : '/initiate';
                upload = await this.request(initiatePath, {
                    file_name: this.file.name,
                    file_size: this.file.size,
                    content_type: this.file.type || (this.kind === 'video' ? 'video/mp4' : 'application/octet-stream'),
                });
                const parts = await this.uploadParts(upload);
                const result = await this.request('/complete', {
                    key: upload.key,
                    upload_id: upload.upload_id,
                    parts,
                });

                if (this.kind === 'video') {
                    this.$wire.set(`${this.statePath}.url`, result.key, false);
                    this.status = 'Video uploaded. Its duration will be calculated after saving the course.';
                } else {
                    this.$wire.set(`${this.statePath}.file_name`, this.file.name, false);
                    this.$wire.set(`${this.statePath}.file_path`, result.key, false);
                    this.$wire.set(`${this.statePath}.file_size`, this.file.size, false);
                    this.status = 'Attachment uploaded successfully.';
                }

                this.progress = 100;
            } catch (error) {
                if (upload?.upload_id) await this.abort(upload).catch(() => {});
                this.error = error.message || 'The file upload failed.';
            } finally {
                this.uploading = false;
            }
        },

        async uploadParts(upload) {
            const cursor = { value: 1 };
            const completed = [];
            const workers = Array.from({ length: Math.min(4, upload.parts_count) }, async () => {
                while (cursor.value <= upload.parts_count) {
                    const partNumber = cursor.value++;
                    const start = (partNumber - 1) * upload.part_size;
                    const blob = this.file.slice(start, Math.min(start + upload.part_size, this.file.size));
                    const signed = await this.request('/sign-part', {
                        key: upload.key,
                        upload_id: upload.upload_id,
                        part_number: partNumber,
                    });
                    completed.push({ part_number: partNumber, etag: await this.putPart(signed.upload_url, blob, partNumber) });
                }
            });
            await Promise.all(workers);
            return completed.sort((left, right) => left.part_number - right.part_number);
        },

        putPart(url, blob, partNumber) {
            return new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                xhr.open('PUT', url);
                xhr.upload.addEventListener('progress', (event) => {
                    if (! event.lengthComputable) return;
                    this.partProgress[partNumber] = event.loaded;
                    const uploaded = Object.values(this.partProgress).reduce((sum, bytes) => sum + bytes, 0);
                    this.uploadedBytes = uploaded;
                    const elapsedSeconds = Math.max((Date.now() - this.startedAt) / 1000, 0.1);
                    this.uploadSpeed = uploaded / elapsedSeconds;
                    this.progress = Math.min(99, Math.round((uploaded / this.file.size) * 100));
                });
                xhr.addEventListener('load', () => {
                    if (xhr.status < 200 || xhr.status >= 300) return reject(new Error(`R2 rejected part ${partNumber}.`));
                    const etag = xhr.getResponseHeader('ETag');
                    etag ? resolve(etag) : reject(new Error('R2 CORS must expose the ETag header.'));
                });
                xhr.addEventListener('error', () => reject(new Error(`Part ${partNumber} could not be uploaded.`)));
                xhr.send(blob);
            });
        },

        abort(upload) {
            return this.request('/abort', { key: upload.key, upload_id: upload.upload_id }, 'DELETE');
        },

        async request(path, body, method = 'POST') {
            const response = await fetch(`${this.baseUrl}${path}`, {
                method,
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify(body),
            });
            if (response.status === 204) return null;
            const data = await response.json().catch(() => ({}));
            if (! response.ok) {
                const validationError = data.errors ? Object.values(data.errors).flat()[0] : null;
                throw new Error(validationError || data.message || 'The upload request failed.');
            }
            return data;
        },

        formatBytes(bytes) {
            if (! bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const unit = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            return `${(bytes / (1024 ** unit)).toFixed(unit ? 2 : 0)} ${units[unit]}`;
        },

        progressSize() {
            return `${this.formatBytes(this.uploadedBytes)} / ${this.formatBytes(this.file?.size ?? 0)}`;
        },

        speedLabel() {
            return this.uploadSpeed > 0 ? `${this.formatBytes(this.uploadSpeed)}/s` : '--';
        },

        etaLabel() {
            if (! this.uploading || this.uploadSpeed <= 0 || ! this.file) return '--';
            const seconds = Math.max(0, Math.ceil((this.file.size - this.uploadedBytes) / this.uploadSpeed));
            if (seconds < 60) return `${seconds}s`;
            return `${Math.floor(seconds / 60)}m ${seconds % 60}s`;
        },
    });

    window.courseVideoUploader = (config) => uploader({ ...config, kind: 'video' });
    window.courseAttachmentUploader = (config) => uploader({ ...config, kind: 'attachment' });
})();
