<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class FillFormController extends Controller
{
    public function index($id)
    {
        $resume = Resume::findOrFail($id);
        // Pass the JSON data to a view for rendering
        $resumeData = json_decode($resume->resume_data, true); // Decode JSON string to array
        return view("fill_form")->with(['id' => $id, 'resumeData' => $resumeData]);
    }

    public function loadProxy()
    {
        return view("proxy");
    }
}
