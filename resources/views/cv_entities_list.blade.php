<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV Entities List</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include jQuery.toast CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="/assets/css/bootstrap.css">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/index_style.css">
</head>
<body>
<div class="container py-5">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="text-center mb-4">Fields & data found</h1>
    <!-- Edit Form -->
    <form id="edit-form" class="mt-4" style="display: none;">
        <!-- Input field for entity name -->
        <div class="mb-3" style="display: none">
            <label for="entity-name" class="form-label">Entity Name</label>
            <input type="text" class="form-control" id="entity-name" placeholder="Enter entity name">
        </div>
        <!-- Textarea for entity value -->
        <div class="mb-3">
            <textarea id="edited-json" class="form-control" rows="5" placeholder="Enter entity value"></textarea>
        </div>
        <button type="submit" class="btn btn-primary my-4">Save Changes</button>
    </form>

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
                    <!-- Edit and Delete Buttons -->
                    <div class="col-md-4 mt-3">
                        <div class="float-right">
                            <button class="btn btn-primary edit-btn btn-sm"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-danger delete-btn btn-sm"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <a href="{{ url('/fill-form/' . $id) }}">
        <button class="btn btn-primary btn-md float-right" id="redirectNextButton">Next <i class="fa-solid fa-angles-right"></i></button>
    </a>

</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include jQuery.toast JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
<script>
    $(document).ready(function () {
        // Edit Button Functionality
        $('.edit-btn').click(function () {
            var value = $(this).closest('.entity-container').find('li, p').map(function () {
                return $(this).text().trim();
                value = value.replace(/,/g, '\n');

            }).get().join('\n');
            var entity = $(this).closest('.entity-container').find('h5').text().trim();

            $('#edited-json').val(value);
            $('#entity-name').val(entity)
            $('#edit-form').show();

            // Scroll the page to the top
            $('html, body').animate({scrollTop: 0}, 'slow');
        });

        // Delete Button Functionality
        $('.delete-btn').click(function () {

            var entity = $(this).closest('.entity-container').find('h5').text().trim();
            $('#entity-name').val(entity)

            var entityName = $('#entity-name').val();

            if (confirm('Are you sure you want to delete this record?')) {
                // Perform delete operation via AJAX
                $.ajax({
                    url: '/resume/{{ $id }}',
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        'entity_name': entityName,
                    },
                    success: function (result) {
                        if (result.success) {
                            $.toast({
                                heading: 'Success',
                                text: 'Field deleted successfully.',
                                icon: 'success',
                                position: 'top-right',
                                loader: false, // Whether to show loader animation
                                loaderBg: '#fff', // Loader background color
                                hideAfter: 9000 // Hide the toast after 3 seconds (adjust as needed)
                            });
                            $('#edit-form').hide();
                            location.reload();
                        }                     },
                    error: function (xhr, status, error) {
                        $.toast({
                            heading: 'Error',
                            text: 'Error deleting record.',
                            icon: 'error',
                            position: 'top-right',
                            loader: false,
                            loaderBg: '#fff',
                            hideAfter: 3000
                        });
                    }
                });
            }
        });

        // Save Changes (Edit) Functionality
        $('#edit-form').submit(function (e) {
            e.preventDefault();
            var entityName = $('#entity-name').val();
            var entityValue = $('#edited-json').val();
            // Perform update operation via AJAX
            $.ajax({
                url: '/resume/{{ $id }}',
                type: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    'entity_name': entityName,
                    'entity_value': entityValue
                },
                success: function (result) {
                    if (result.success) {
                        $.toast({
                            heading: 'Success',
                            text: 'Field updated successfully.',
                            icon: 'success',
                            position: 'top-right',
                            loader: false, // Whether to show loader animation
                            loaderBg: '#fff', // Loader background color
                            hideAfter: 9000 // Hide the toast after 3 seconds (adjust as needed)
                        });
                        $('#edit-form').hide();
                        location.reload();
                    }
                },
                error: function (xhr, status, error) {
                    $.toast({
                        heading: 'Error',
                        text: xhr.error,
                        icon: 'error',
                        position: 'top-right',
                        loader: false,
                        loaderBg: '#fff',
                        hideAfter: 3000
                    });
                }
            });
        });
        // for net button
        document.getElementById("redirectNextButton").addEventListener("click", function() {
            // Redirect to the desired URL
            window.location.href = "{{ route('fill-form', ['id' => $id]) }}";
        });
    });
</script>
</body>
</html>
