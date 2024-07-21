<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenAI;

class FileUploadController extends Controller
{
    public function index()
    {
        return view("index");
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Maximum file size in kilobytes (50MB in this example)
        ]);

        $file = $request->file('file');

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($request->file('file')->getPathname());
        $text = $pdf->getText();
        $isResumeOrCV = $this->containsResumeOrCVTerms($text);
        if ($isResumeOrCV) {
            // Your OpenAI API key
            $apiKey = env('OPENAI_API_KEY');

            // Prompt provided by the user
// Prompt to be added before the user's text for formatting
// Prompt to be added before the user's text for formatting
            $prompt = '
            Process the extracted text according to the following instructions:
1. Remove special characters, formatting, and noise from the text.
2. Ensure consistent formatting and handle line breaks appropriately.
3. Break down the text into individual tokens such as words or phrases for further analysis.
4. Tell me every possible field of data and what that data is. For example: surname: halls, Name, Email, Mobile No.

Once the text has been processed,only provide JSON, provide only the JSON representation of the extracted field of data with separate headings but not nested First name, Middle name, Last name, Date of birth, Linkedin, Email, Mobile No, Address, Work Experience, Education, Expertise, Languages, Work history, Street address, About, Skills Summary, City, State, Country, Postal Code, References, Honors awards.

            ';

            // Make API call to OpenAI
            $client = OpenAI::client($apiKey);
            $result = $client->chat()->create([
                'model' => 'gpt-4', // Use GPT-4 model
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $text],
                ],
            ]);

            // Extract the response
            $response = $result->choices[0]->message->content; // Hello! How can I assist you today?
            // Check if the request was successful
            if ($response) {
                $resume = new Resume();
                $resume->resume_data = $response;
                $resume->save();
                return response()->json(['success' => 'Resume extracted successfully', 'status' => 200,'redirect_url' => 'resume/' . $resume->id]);
                // Return error message if API call fails
            } else {
                return response()->json(['error' => 'API call failed', 'status' => 404]);
            }

        } else {
            return response()->json(['error' => 'The provided document does not appear to be a resume or CV', 'status' => 404]);
        }
        //$path = $file->store('uploads', 'public');
    }

    private function containsResumeOrCVTerms($text)
    {
        // List of common resume or CV terms
        $resumeCVTerms = ['experience', 'education', 'skills', 'work history', 'objective', 'summary', 'achievements'];

        // Check if any of the terms exist in the text
        foreach ($resumeCVTerms as $term) {
            if (stripos($text, $term) !== false) {
                return true;
            }
        }

        return false;
    }
}
