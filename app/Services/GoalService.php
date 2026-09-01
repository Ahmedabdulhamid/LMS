<?php

namespace App\Services;

use App\Models\Course;

class GoalService
{
    public function syncGoals(Course $course, array $goals): void
    {
        $existingGoals = $course->goals()->get()->keyBy('id');
        $goalIds = [];

        foreach ($goals as $goal) {
            if (! empty($goal['id']) && $existingGoals->has($goal['id'])) {
                $existingGoal = $existingGoals->get($goal['id']);
                $existingGoal->update([
                    'goal' => $goal['goal'],
                    'order' => $goal['order'],
                ]);
                $goalIds[] = $existingGoal->id;
            } else {
                $newGoal = $course->goals()->create([
                    'goal' => $goal['goal'],
                    'order' => $goal['order'],
                ]);
                $goalIds[] = $newGoal->id;
            }
        }

        if ($goalIds === []) {
            $existingGoals->each->delete();
        } else {
            $existingGoals->except($goalIds)->each->delete();
        }
    }
}
