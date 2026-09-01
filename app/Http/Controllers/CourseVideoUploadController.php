<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseVideos\AbortCourseVideoUploadRequest;
use App\Http\Requests\CourseVideos\CompleteCourseVideoUploadRequest;
use App\Http\Requests\CourseVideos\InitiateCourseAttachmentUploadRequest;
use App\Http\Requests\CourseVideos\InitiateCourseVideoUploadRequest;
use App\Http\Requests\CourseVideos\SignCourseVideoPartRequest;
use App\Models\Course;
use App\Services\R2VideoUploadService;
use Illuminate\Http\JsonResponse;

class CourseVideoUploadController extends Controller
{
    public function __construct(private readonly R2VideoUploadService $videoUploadService) {}

    public function initiate(InitiateCourseVideoUploadRequest $request, Course $course): JsonResponse
    {
        return response()->json($this->videoUploadService->initiate(
            (int) $request->user('instructor')->getAuthIdentifier(),
            $course->id,
            $request->validated(),
        ), 201);
    }

    public function initiateAttachment(InitiateCourseAttachmentUploadRequest $request, Course $course): JsonResponse
    {
        return response()->json($this->videoUploadService->initiateAttachment(
            (int) $request->user('instructor')->getAuthIdentifier(),
            $course->id,
            $request->validated(),
        ), 201);
    }

    public function signPart(SignCourseVideoPartRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->videoUploadService->signPart(
            (int) $request->user('instructor')->getAuthIdentifier(),
            $course->id,
            $data['key'],
            $data['upload_id'],
            $data['part_number'],
        ));
    }

    public function complete(CompleteCourseVideoUploadRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->videoUploadService->complete(
            (int) $request->user('instructor')->getAuthIdentifier(),
            $course->id,
            $data['key'],
            $data['upload_id'],
            $data['parts'],
        ));
    }

    public function abort(AbortCourseVideoUploadRequest $request, Course $course): JsonResponse
    {
        $data = $request->validated();

        $this->videoUploadService->abort(
            (int) $request->user('instructor')->getAuthIdentifier(),
            $course->id,
            $data['key'],
            $data['upload_id'],
        );

        return response()->json(status: 204);
    }
}
