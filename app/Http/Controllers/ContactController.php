<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(public ContactService $contactService)
    {
        //
    }
    public function store(ContactRequest $request)
    {
        $validatedData = $request->validated();

        $contact = $this->contactService->storeContact($validatedData);

        return
    }
}
