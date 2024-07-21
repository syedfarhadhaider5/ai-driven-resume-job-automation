<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CvDetailExtractController extends Controller
{
    public function ShowCvList($id)
    {
        $resume = Resume::findOrFail($id);

        // Pass the JSON data to a view for rendering
        $resumeData = json_decode($resume->resume_data, true); // Decode JSON string to array
        return view('cv_entities_list', ['resumeData' => $resumeData, 'id' => $id]);
    }

    public function CvListUpdate(Request $request, $id)
    {
        // Validate the request if necessary

        $resume = Resume::findOrFail($id);
        $resumeData = json_decode($resume->resume_data, true); // Decode JSON string to array

        // Check if the decoded data is an array
        if (is_array($resumeData)) {
            // Retrieve the entity name and value from the request
            $entityName = $request->input('entity_name');
            $entityValue = $request->input('entity_value');

            // Check if the entity name exists in the resume data
            if (array_key_exists($entityName, $resumeData)) {
                // Update the entity value
                if (is_array($entityValue)) {
                    // If the input value is an array, use it directly
                    $resumeData[$entityName] = $entityValue;
                } else {
                    // Otherwise, parse the input value into an array of strings
                    $resumeData[$entityName] = explode("\n", $entityValue);
                    // Trim each element of the array
                    $resumeData[$entityName] = array_map('trim', $resumeData[$entityName]);
                }

                // Encode the updated array back into JSON format
                $updatedResumeData = json_encode($resumeData);

                // Update the database record with the new JSON data
                $resume->update(['resume_data' => $updatedResumeData]);

                return response()->json(['success' => true], 200);
            } else {
                // Handle the case where the entity name does not exist
                return response()->json(['error' => 'Entity not found'], 404);
            }
        } else {
            // Handle the case where the decoded data is not an array
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    }

    public function CvDataUpdate(Request $request, $id)
    {
        // Validate the request if necessary

        $resume = Resume::findOrFail($id);
        $resumeData = json_decode($resume->resume_data, true); // Decode JSON string to array

        // Check if the decoded data is an array
        if (is_array($resumeData)) {
            // Retrieve the entity name from the request
            $entityName = $request->input('entity_name');

            // Check if the entity name exists in the resume data
            if (array_key_exists($entityName, $resumeData)) {
                // Remove the entity from the resume data
                unset($resumeData[$entityName]);

                // Encode the updated array back into JSON format
                $updatedResumeData = json_encode($resumeData);

                // Update the database record with the new JSON data
                $resume->update(['resume_data' => $updatedResumeData]);

                return response()->json(['success' => true], 200);
            } else {
                // Handle the case where the entity name does not exist
                return response()->json(['error' => 'Entity not found'], 404);
            }
        } else {
            // Handle the case where the decoded data is not an array
            return response()->json(['error' => 'Invalid data format'], 400);
        }
    }

}
