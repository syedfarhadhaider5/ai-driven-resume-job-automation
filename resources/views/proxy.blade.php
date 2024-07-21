<?php
$id = $_GET['id'] ?? null;

if (!$id) {
    error_log("Error: ID parameter is missing or empty.");
    echo "<script>alert('Error: ID parameter is missing or empty.')</script>";
    exit();
}

if (isset($_GET['url']) && !empty($_GET['url'])) {
    $url = $_GET['url'];
    $apiKey = 'sk-proj-GTzhFiXt2bllRSGGxxKQT3BlbkFJihdJ4rQZnHbOsH1hxr7p';

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        $response = \Illuminate\Support\Facades\Http::get($url);

        if ($response->ok()) {
            $dom = new DOMDocument();
            @$dom->loadHTML($response->body());

            $resumes = \App\Models\Resume::find($id);
            if ($resumes) {
                $jsonData = json_decode($resumes->resume_data, true);

                if ($jsonData !== null) {
                    $labelsAndNames = [];
                    $inputElements = $dom->getElementsByTagName('input');
                    $labelElements = $dom->getElementsByTagName('label');
                    $textareaElements = $dom->getElementsByTagName('textarea');
                    $selectElements = $dom->getElementsByTagName('select');

                    $labels = [];
                    foreach ($labelElements as $label) {
                        if ($label->hasAttribute('for')) {
                            $labels[$label->getAttribute('for')] = $label->textContent;
                        }
                    }

                    foreach ($inputElements as $input) {
                        if ($input->hasAttribute('id') && isset($labels[$input->getAttribute('id')])) {
                            $labelsAndNames[$labels[$input->getAttribute('id')]] = $input->getAttribute('name');
                        }
                    }

                    foreach ($textareaElements as $textarea) {
                        if ($textarea->hasAttribute('id') && isset($labels[$textarea->getAttribute('id')])) {
                            $labelsAndNames[$labels[$textarea->getAttribute('id')]] = $textarea->getAttribute('name');
                        }
                    }

                    foreach ($selectElements as $select) {
                        if ($select->hasAttribute('id') && isset($labels[$select->getAttribute('id')])) {
                            $labelsAndNames[$labels[$select->getAttribute('id')]] = $select->getAttribute('name');
                        }
                    }

                    $prompt = "You are given an original JSON dataset and a list of input labels mapped to input names from a form. Your task is to create a new JSON object where the keys are the form input labels, and the values are intelligently mapped from the corresponding keys in the original JSON dataset using natural language processing (NLP). Ensure that specific fields like 'City', 'State / Province', and 'Postal / Zip Code' are accurately filled with the appropriate values from the original JSON data. If a key is not present in the original JSON, set its value as an empty string or an appropriate empty value (e.g., empty array for lists). Discard any old keys not present in the new input labels. Use NLP to understand the context and accurately map relevant data. Below is an example of the original JSON and the list of input labels mapped to input names.\n\n" .
                        "Original JSON data:\n\n" . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n\n" .
                        "Input labels and names:\n\n" . json_encode($labelsAndNames, JSON_PRETTY_PRINT) . "\n\n" .
                        "The final JSON structure should match the input labels exactly and preserve values from the original JSON where applicable. Only provide JSON not any extra text included. If an input name corresponds to an array in the original JSON, convert it to a comma-separated string.";

                    $client = OpenAI::client($apiKey);
                    $result = $client->chat()->create([
                        'model' => 'gpt-4',
                        'messages' => [
                            ['role' => 'system', 'content' => $prompt],
                        ],
                    ]);

                    if (isset($result->choices[0]->message->content)) {
                        $openAIResponse = $result->choices[0]->message->content;
                        $newJsonData = json_decode($openAIResponse, true);

                        if ($newJsonData !== null) {
                            $resumes->resume_new_polish_data = json_encode($newJsonData);
                            $resumes->save();

                            foreach ($newJsonData as $jsonKey => $fieldValue) {
                                if (is_array($fieldValue)) {
                                    $fieldValue = implode(', ', $fieldValue);
                                }

                                foreach ($inputElements as $input) {
                                    if ($input->hasAttribute('id') && isset($labels[$input->getAttribute('id')]) && $labels[$input->getAttribute('id')] == $jsonKey) {
                                        $input->setAttribute('value', (string)$fieldValue);
                                    }
                                }

                                foreach ($textareaElements as $textarea) {
                                    if ($textarea->hasAttribute('id') && isset($labels[$textarea->getAttribute('id')]) && $labels[$textarea->getAttribute('id')] == $jsonKey) {
                                        $textarea->textContent = (string)$fieldValue;
                                    }
                                }

                                foreach ($selectElements as $select) {
                                    if ($select->hasAttribute('id') && isset($labels[$select->getAttribute('id')]) && $labels[$select->getAttribute('id')] == $jsonKey) {
                                        foreach ($select->getElementsByTagName('option') as $option) {
                                            if ($option->getAttribute('value') == $fieldValue) {
                                                $option->setAttribute('selected', 'selected');
                                            } else {
                                                $option->removeAttribute('selected');
                                            }
                                        }
                                    }
                                }
                            }

                            $modifiedHtml = $dom->saveHTML();
                            echo $modifiedHtml;
                        } else {
                            error_log("Error: Unable to decode JSON response from OpenAI.");
                            echo "<script>alert('Error: Failed to decode JSON response from OpenAI.')</script>";
                        }
                    } else {
                        error_log("Error: Invalid response from OpenAI");
                        echo "<script>alert('Error: Failed to generate updated JSON data.')</script>";
                    }
                } else {
                    error_log("Error: Unable to decode JSON data from the database.");
                    echo "<script>alert('Error: Failed to decode JSON data from the database.')</script>";
                }
            } else {
                error_log("Error: Resume data not found in the database.");
                echo "<script>alert('Error: Resume data not found in the database.')</script>";
            }
        } else {
            error_log("Error: Failed to fetch the content from the URL.");
            echo "<script>alert('Error: Failed to fetch the content from the URL.')</script>";
        }
    } else {
        error_log("Error: Invalid URL.");
        echo "<script>alert('Error: Invalid URL.')</script>";
    }
} else {
    error_log("Error: URL parameter is missing or empty.");
    echo "<script>alert('Error: URL parameter is missing or empty.')</script>";
}
?>
