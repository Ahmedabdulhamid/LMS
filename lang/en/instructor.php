<?php

return [
    'navigation' => [
        'dashboard' => 'Dashboard', 'courses' => 'Courses', 'students' => 'Students',
        'payments' => 'Payments', 'reviews' => 'Reviews', 'profile' => 'Profile',
    ],
    'dashboard' => [
        'title' => 'Dashboard',
        'stats' => ['courses' => 'Total Courses', 'students' => 'Total Students', 'revenue' => 'Total Revenue', 'rating' => 'Average Rating'],
        'recent_enrollments' => 'Recent Enrollments',
        'course_performance' => 'Course Performance',
        'columns' => ['student' => 'Student', 'course' => 'Course', 'enrollment_date' => 'Enrollment date', 'course_title' => 'Course title', 'students' => 'Students', 'completion' => 'Completion', 'rating' => 'Average rating'],
        'empty' => ['enrollments' => 'No enrollments yet', 'courses' => 'No course performance data yet'],
    ],
    'resources' => [
        'course' => ['singular' => 'Course', 'plural' => 'Courses'],
        'student' => ['singular' => 'Student', 'plural' => 'Students'],
        'payment' => ['singular' => 'Payment', 'plural' => 'Payments'],
        'review' => ['singular' => 'Review', 'plural' => 'Reviews'],
    ],
    'tables' => [
        'common' => ['student' => 'Student', 'course' => 'Course', 'email' => 'Email', 'status' => 'Status', 'created_at' => 'Created at'],
        'students' => ['courses' => 'Courses', 'joined' => 'Joined', 'empty_heading' => 'No students yet', 'empty_description' => 'Students enrolled in your courses will appear here.'],
        'payments' => ['price' => 'Amount', 'date' => 'Payment date', 'empty_heading' => 'No payments yet', 'empty_description' => 'Course payments will appear here.'],
        'reviews' => ['rating' => 'Rating', 'comment' => 'Comment', 'approved' => 'Approved', 'reviewed' => 'Reviewed', 'empty_heading' => 'No reviews yet', 'empty_description' => 'Student reviews will appear here.'],
    ],
    'statuses' => ['completed' => 'Completed', 'pending' => 'Pending', 'failed' => 'Failed'],
    'languages' => ['ar' => 'Arabic', 'en' => 'English', 'fr' => 'French'],
    'levels' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'],
    'courses' => [
        'sections' => ['details' => 'Course details', 'learning_goals' => 'Learning goals', 'requirements' => 'Requirements', 'curriculum' => 'Curriculum'],
        'fields' => ['title' => 'Title', 'category' => 'Category', 'language' => 'Language', 'level' => 'Level', 'published' => 'Published', 'price' => 'Price', 'discount_price' => 'Price after discount', 'description' => 'Description', 'thumbnail' => 'Thumbnail', 'goal' => 'Learning goal', 'requirement' => 'Requirement', 'section_title' => 'Section title', 'videos' => 'Videos', 'video_title' => 'Video title', 'video_description' => 'Video description', 'video_file' => 'Video file', 'is_free' => 'Free preview', 'lessons' => 'Lessons', 'duration' => 'Duration', 'updated' => 'Updated'],
        'help' => ['r2_upload_hint' => 'Save the course first, then edit it to upload long videos directly to R2.'],
        'empty_heading' => 'No courses yet', 'empty_description' => 'Create your first course to start teaching.',
        'actions' => ['create' => 'Create course', 'edit' => 'Edit course', 'view' => 'View course', 'delete' => 'Delete course', 'delete_confirm' => 'Are you sure you want to delete :course?', 'delete_success' => 'Course deleted successfully.', 'add_goal' => 'Add goal', 'add_requirement' => 'Add requirement', 'add_section' => 'Add section', 'add_video' => 'Add video'],
        'notifications' => ['created' => 'Course created successfully.'],
    ],
    'profile' => ['navigation' => 'Profile', 'title' => 'My instructor profile'],
    'uploader' => ['choose' => 'Choose video file', 'upload' => 'Upload video to R2', 'uploading' => 'Uploading :progress%', 'save_first' => 'Save the course first, then edit it to upload long videos directly to R2.'],
    'language_switcher' => ['label' => 'Language', 'english' => 'English', 'arabic' => 'العربية'],
];
