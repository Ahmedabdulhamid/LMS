<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\VerifyEmail as CustomVerifyEmail;
use Filament\Auth\Notifications\VerifyEmail as FilamentVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationDesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_the_custom_verification_prompt(): void
    {
        $student = User::factory()->unverified()->create(['is_active' => true]);

        $this->actingAs($student, 'student')
            ->withSession(['locale' => 'en'])
            ->get(route('filament.students.auth.email-verification.prompt'))
            ->assertOk()
            ->assertSee(__('email-verification.page.heading'))
            ->assertSee($student->email)
            ->assertSee(__('email-verification.page.resend'));
    }

    public function test_filament_uses_the_custom_verification_email(): void
    {
        $student = User::factory()->unverified()->create();
        $notification = app(FilamentVerifyEmail::class);

        $this->assertInstanceOf(CustomVerifyEmail::class, $notification);

        $notification->url = 'https://example.test/verify-email';
        $mail = $notification->toMail($student);

        $this->assertSame('emails.email-verification', $mail->view);
        $this->assertStringContainsString('Verify your email address', $mail->render());
        $this->assertStringContainsString($notification->url, $mail->render());
    }
}
