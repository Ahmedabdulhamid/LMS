<?php

namespace App\Http\Requests\CourseVideos;

class CompleteCourseVideoUploadRequest extends CourseVideoUploadRequest
{
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:1024'],
            'upload_id' => ['required', 'string', 'max:2048'],
            'parts' => ['required', 'array', 'min:1', 'max:10000'],
            'parts.*.part_number' => ['required', 'integer', 'between:1,10000', 'distinct'],
            'parts.*.etag' => ['required', 'string', 'max:255'],
        ];
    }
}
