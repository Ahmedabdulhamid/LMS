<?php

namespace Tests\Feature;

use App\Filament\Sudents\Pages\Auth\Register;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StudentRegistrationValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_phone_is_reported_as_validation_error(): void
    {
        User::factory()->create(['phone' => '01065172788']);
        Filament::setCurrentPanel(Filament::getPanel('students'));

        Livewire::test(Register::class)
            ->fillForm([
                'name' => 'Another Student',
                'email' => 'another-student@example.com',
                'password' => 'Password123!',
                'passwordConfirmation' => 'Password123!',
                'phone' => '01065172788',
            ])
            ->call('register')
            ->assertHasFormErrors(['phone' => 'unique']);

        $this->assertDatabaseMissing('users', ['email' => 'another-student@example.com']);
    }
}
