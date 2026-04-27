<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        // Validate and process the incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'location' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
        ]);

        // Here you can handle the validated data, e.g., save it to the database

        return response()->json(['message' => 'Lead submitted successfully!']);
    }
}