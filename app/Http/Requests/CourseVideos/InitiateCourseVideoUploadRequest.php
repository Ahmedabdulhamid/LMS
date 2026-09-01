<?php

namespace App\Http\Requests\CourseVideos;

class InitiateCourseVideoUploadRequest extends CourseVideoUploadRequest
{
    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string', 'max:255', 'regex:/\.(mp4|mov|m4v|webm|mkv)$/i'],
            'file_size' => ['required', 'integer', 'min:1', 'max:5497558138880'],
            'content_type' => ['required', 'string', 'max:100', 'regex:/^video\/[a-z0-9.+-]+$/i'],
        ];
    }
}
