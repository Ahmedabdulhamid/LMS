<?php

namespace App\Http\Requests\CourseVideos;

class SignCourseVideoPartRequest extends CourseVideoUploadRequest
{
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:1024'],
            'upload_id' => ['required', 'string', 'max:2048'],
            'part_number' => ['required', 'integer', 'between:1,10000'],
        ];
    }
}
