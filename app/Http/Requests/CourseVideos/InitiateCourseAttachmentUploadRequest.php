<?php

namespace App\Http\Requests\CourseVideos;

class InitiateCourseAttachmentUploadRequest extends CourseVideoUploadRequest
{
    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string', 'max:255', 'regex:/\.(pdf|zip|rar|7z|doc|docx|ppt|pptx|xls|xlsx|txt|csv)$/i'],
            'file_size' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'content_type' => ['required', 'string', 'max:150'],
        ];
    }
}
