<?php

namespace App\Livewire;

use App\Services\ContactService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ContactPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $subject = '';
    public string $message = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'subject' => 'nullable|string|max:255',
        'message' => 'required|string|max:5000',
    ];

    public function submit(ContactService $contactService): void
    {
        $contactService->storeContact($this->validate(), app()->getLocale());

        session()->flash('success', __('contacts.form.success'));

        $this->reset();
    }

    public function render(): View
    {
        return view('livewire.contact-page')->layout('layouts.course-public');
    }
}
