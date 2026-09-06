<?php

use App\Services\ContactService;
use Livewire\Component;

new class extends Component
{
    public $name;
    public $email;
    public $phone;
    public $message;
    public $subject;
    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'message' => 'required|string',
        'subject' => 'nullable|string|max:255',
    ];
    public function submit(ContactService $contactService)
    {
       $data= $this->validate();
        $contactService->storeContact($data);


        session()->flash('success', 'Your message has been sent successfully!');

        // Reset form fields
        $this->reset();
    }
    public function render()
    {
        return view('components.⚡contact.contact');
    }
};
