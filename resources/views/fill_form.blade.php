<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Entities List</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/index_style.css">
    <style>
        .loading-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px; /* Adjust size as needed */
            height: 50px; /* Adjust size as needed */
            border: 3px solid #3498db; /* Blue border */
            border-radius: 50%;
            border-top-color: #555; /* Dark gray border on top */
            animation: spin 1s infinite linear; /* Rotate animation */
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* semi-transparent white background */
            z-index: 9999; /* Ensure it's on top of other content */
            display: none; /* Initially hidden */
        }
    </style>
</head>
<body>
<div id="loading-overlay">
    <div class="loading-icon"></div>
</div>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 col-sm-12 mt-3">
            <div
                style="background-color: white; padding: 14px 12px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); border-radius: 50px;">
                <div class="input-group custom-input">
                    <input type="url" class="form-control" placeholder="Enter URL" id="urlInput">
                    <div class="input-group-append">
                        <button class="btn btn-primary" style="border-radius: 100px" type="button" id="submitBtn"><i
                                class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <iframe id="form-frame" frameborder="0" style="width: 100%; height: 500px;"></iframe>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <!-- Display Entities -->
            @foreach ($resumeData as $entity => $value)
                @if((is_array($value) && count($value) > 0) || (is_string($value) && trim($value) !== ''))
                    <div class="row entity-container">
                        <div class="col-md-8">
                            <h5>{{ $entity }}</h5>
                            @if (is_array($value))
                                <ul class="list-unstyled">
                                    @foreach ($value as $item)
                                        @if (is_array($item))
                                            @foreach ($item as $subKey => $subValue)
                                                <li>{{ is_string($subValue) ? htmlspecialchars($subValue) : '' }}</li>
                                            @endforeach
                                        @else
                                            <li>{{ htmlspecialchars($item) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            @elseif (is_string($value))
                                <p>{{ htmlspecialchars($value) }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

        </div>
    </div>
</div>

<script>
    document.getElementById('submitBtn').addEventListener('click', function () {
        var formUrl = document.getElementById('urlInput').value;
        var proxyUrl = 'http://127.0.0.1:8000/proxy?id=' + '<?php echo $id; ?>' + '&url=' + encodeURIComponent(formUrl);
        var frame = document.getElementById('form-frame');
        var loadingOverlay = document.getElementById('loading-overlay');

        // Show loading overlay
        loadingOverlay.style.display = 'block';

        // Load form content using proxy
        frame.src = proxyUrl;

        // Wait for iframe to load
        frame.onload = function () {
            loadingOverlay.style.display = 'none';
            document.getElementById('urlInput').value = "";
        };

        // Handle errors during iframe loading
        frame.onerror = function () {
            loadingOverlay.style.display = 'none';
            alert('Failed to load the form from the provided URL.');
        };
    });
</script>
</body>
</html>
