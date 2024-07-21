<!-- upload.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Form Autofill with Document Data Extraction</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include jQuery.toast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">
    <!-- Include Bootstrap CSS from CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/index_style.css">

</head>
<body>

<div class="container">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row" style="margin-top: 50px;">
        <div class="col-md-1 col-sm-12"></div>
        <div class="col-md-10 col-sm-12 left-side-area">
            <div class="row">
                {{-- first part --}}
                <div class="col-md-12 col-sm-12">
                    <h3 class="text-center my-4">AI Form Autofill with Document Data Extraction</h3>
                </div>
                <div class="col-md-6 col-sm-12 ">
                    <div id="drop-area">
                        <p><i class="fa-regular fa-file-pdf"
                              style="color: #FF0000; font-size: 5rem; margin: 10px 10px"></i></p>
                        <h5>Drag & Drop Files to upload</h5>
                        <p>or</p>
                        <label class="btn btn-primary">
                            Browse <input type="file" id="fileInput" accept="application/pdf"
                                          style="display: none;">
                        </label>
                    </div>
                </div>
                {{-- second part --}}
                <div class="col-md-6 col-sm-12">
                    <div class="mt-5">
                        <div id="file-list" class="mt-3">No file uploaded.</div>
                        <div id="progress-bar-container" style="display: none;">
                            <div class="progress">
                                <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span id="status" class="float-right">0%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-1 col-sm-12"></div>
    </div>
    <div id="loading-overlay">
        <div class="loading-icon"></div>
    </div>
</div>

<!-- Include jQuery and Bootstrap JS from CDN -->
<!-- Include jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Include jQuery.toast JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
<script>
    // Define removeFile function globally
    function removeFile(index) {
        $('#file-' + index).remove();
        $('#uploadBtn').hide();
    }
    // Show loading overlay
    function showLoading() {
        $('#loading-overlay').show();
    }

    // Hide loading overlay
    function hideLoading() {
        $('#loading-overlay').hide();
    }
    $(document).ready(function () {
        // CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        // Drag & Drop functionality
        var dropArea = document.getElementById('drop-area');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        if (dropArea.classList.contains('highlight')) {
            console.log('Class "highlight" has been added.');
        } else {
            console.log('Class "highlight" has not been added.');
        }
        function highlight(e) {
            dropArea.classList.add('highlight');
        }

        function unhighlight(e) {
            dropArea.classList.remove('highlight');
        }

        dropArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            var files = e.dataTransfer.files;
            handleFiles(files);
        }

        // File Input functionality
        var fileInput = document.getElementById('fileInput');
        fileInput.addEventListener('change', function () {
            var files = this.files;
            handleFiles(files);
        });

        // File upload function
        function handleFiles(files) {
            $('#progress-bar-container').hide();
            $('#file-list').empty();
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (file.size > 50 * 1024 * 1024) { // Check file size (in bytes)
                    $.toast({
                        heading: 'Error',
                        text: 'File size exceeds 50MB limit',
                        icon: 'error',
                        position: 'top-right',
                        loader: false,
                        loaderBg: '#fff',
                        hideAfter: 3000
                    });
                    continue; // Skip this file
                }
                if (file.type !== 'application/pdf') { // Check file type
                    //file.name
                    $.toast({
                        heading: 'Error',
                        text: 'Only PDF files are allowed',
                        icon: 'error',
                        position: 'top-right',
                        loader: false,
                        loaderBg: '#fff',
                        hideAfter: 3000
                    });
                    continue; // Skip this file
                }
                $('#file-list').append('<div id="file-' + i + '" class="file-info">' + file.name + ' <button class="btn btn-danger btn-sm" ><i onclick="removeFile(' + i + ')" class="fa-solid fa-trash-can"></i></button></div>');
                uploadFile(file);
            }
        }

        function uploadFile(file) {
            var formData = new FormData();
            formData.append('file', file);

            $.ajax({
                url: '{{ route('upload') }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            var percentComplete = (e.loaded / e.total) * 100;
                            $('#progress-bar').css('width', percentComplete + '%');
                            $('#status').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                beforeSend: showLoading, // Show loading overlay before sending request
                success: function (response) {
                    //console.log(response.success);
                    if(response.status === 200){
                        $.toast({
                            heading: 'Success',
                            text: response.success,
                            icon: 'success',
                            position: 'top-right',
                            loader: false, // Whether to show loader animation
                            loaderBg: '#fff', // Loader background color
                            hideAfter: 3000 // Hide the toast after 3 seconds (adjust as needed)
                        });
                        window.location.href = response.redirect_url;
                    }else{
                        $.toast({
                            heading: 'Error',
                            text: response.error,
                            icon: 'error',
                            position: 'top-right',
                            loader: false,
                            loaderBg: '#fff',
                            hideAfter: 3000
                        });
                    }

                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                    // Handle error
                    var errorMessage;
                    try {
                        var responseJson = JSON.parse(xhr.responseText);
                        errorMessage = responseJson.error;
                    } catch (e) {
                        errorMessage = "An unexpected error occurred.";
                    }
                    $.toast({
                        heading: 'Error',
                        text: errorMessage,
                        icon: 'error',
                        position: 'top-right',
                        loader: false,
                        loaderBg: '#fff',
                        hideAfter: 3000
                    });
                },
                complete: hideLoading // Hide loading overlay when request completes
            });
        }
    });
</script>


</body>
</html>
