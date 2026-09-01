<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Section;

class SectionService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CalcalateCourseDurationService $calcalateCourseDurationService)
    {
        //
    }

    public function syncSections(Course $course, array $sections): void
    {
        $existingSections = $course->sections()->get()->keyBy('id');
        $sectionIds = [];
        foreach ($sections as $section) {
            if (! empty($section['id']) && $existingSections->has($section['id'])) {
                $existingSection = $existingSections->get($section['id']);
                $existingSection->update([
                    'title' => $section['title'],
                    'order' => $section['order'],
                ]);
                $sectionIds[] = $existingSection->id;
            } else {
                $newSection = $course->sections()->create([
                    'title' => $section['title'],
                    'order' => $section['order'],
                ]);
                $sectionIds[] = $newSection->id;
            }
        }
        if (count($sectionIds) === 0) {
            $existingSections->each->delete();
        } else {
            $existingSections->except($sectionIds)->each->delete();
        }
        $this->calcalateCourseDurationService->calculateCourseDuration($course);
    }

    public function deleteSection(int $sectionId): void
    {
        $section = Section::findOrFail($sectionId);
        $course = $section->course;
        $section->delete();
        $this->calcalateCourseDurationService->calculateCourseDuration($course);

    }

    public function updateSection(int $sectionId, array $data): void
    {
        $section = Section::findOrFail($sectionId);
        $section->update([
            'title' => $data['title'],
            'order' => $data['order'],
        ]);
        $this->calcalateCourseDurationService->calculateCourseDuration($section->course);
    }
}
