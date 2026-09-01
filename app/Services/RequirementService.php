<?php

namespace App\Services;

use App\Models\Course;

class RequirementService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function syncRequirements(Course $course, array $data): void
    {
        $existingRequirements = $course->requirements()->get()->keyBy('id');
        $requirementIds = [];
        foreach ($data as $requirement) {
            if (! empty($requirement['id']) && $existingRequirements->has($requirement['id'])) {
                $existingRequirement = $existingRequirements->get($requirement['id']);
                $existingRequirement->update([
                    'content' => $requirement['content'],
                ]);
                $requirementIds[] = $existingRequirement->id;
            } else {
                $newRequirement = $course->requirements()->create([
                    'content' => $requirement['content'],
                ]);
                $requirementIds[] = $newRequirement->id;
            }
        }
        if (count($requirementIds) === 0) {
            $existingRequirements->each->delete();
        } else {
            $existingRequirements->except($requirementIds)->each->delete();
        }
    }
}
