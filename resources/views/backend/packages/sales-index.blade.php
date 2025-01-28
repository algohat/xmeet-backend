@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <!--begin::Card-->
                <div class="card mb-5 shadow-lg border-0 rounded-4">
                    <!--begin::Card header-->
                    <div class="card-header bg-gallery text-white py-4 rounded-top">
                        <h3 class="card-title mb-0 text-center">Sales Statistics</h3>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body py-5">
                        <!-- Total Earnings Section -->
                        <div class="row mb-5">
                            <div class="col-md-12">
                                <div class="alert alert-success text-center p-4 rounded-3 shadow-sm">
                                    <h4 class="fw-bold mb-0">
                                        <i class="fas fa-dollar-sign me-2"></i>
                                        Total Earnings: ${{ number_format($totalSales, 2) }} USD
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- Package Sales Details -->
                        <div class="row">
                            @foreach ($packageSales as $sale)
                                <div class="col-md-4 mb-4">
                                    <div class="card border-0 shadow-sm h-100 rounded-5">
                                        <div class="card-body p-4">
                                            <!-- Package Name -->
                                            <h5 class="card-title text-primary fw-bold mb-3">
                                                {{ $sale['package_name'] }}
                                            </h5>

                                            <!-- Package Details -->
                                            <ul class="list-unstyled mb-3">
                                                <li>
                                                    <span class="text-muted">Type:</span>
                                                    <span class="fw-medium">{{ $sale['package_type'] }}</span>
                                                </li>
                                                <li>
                                                    <span class="text-muted">Tag:</span>
                                                    <span class="fw-medium">{{ $sale['package_tag'] }}</span>
                                                </li>
                                                <li>
                                                    <span class="text-muted">Validation:</span>
                                                    <span class="fw-medium">{{ $sale['validation_time'] }}</span>
                                                </li>
                                            </ul>

                                            <!-- Sales Info -->
                                            <div class="text-muted">
                                                <p class="mb-2">
                                                    <strong>Total Sales Amount:</strong>
                                                    ${{ number_format($sale['total_sales'], 2) }} USD
                                                </p>
                                                <p>
                                                    <strong>Sales Count:</strong>
                                                    {{ $sale['count'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>


        </div>
    </div>



    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>


    <script>
        $(document).ready(function () {
            let baseUrl = `{{ url('admin/packages/feature') }}`;
            // Add or Update Item
            $('#saveButton').click(function () {
                let formData = $('#packageForm').serialize();
                let packageId = $('#packageId').val();
                let url = packageId
                    ? `${baseUrl}/update/${packageId}` // Update route
                    : `${baseUrl}/store`;             // Store route

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        toastr.success('Package saved successfully.');
                        location.reload();
                    },
                    error: function (response) {
                       toastr.error('Something went wrong.');
                    }
                });
            });

            // Edit Item
            $('.edit-button').click(function () {
                let row = $(this).closest('tr'); // Get the current table row
                let id = row.data('id'); // ID of the feature
                let feature_type = row.find('td:eq(2)').text().trim(); // Feature Type (2nd column)
                let title = row.find('td:eq(3)').text().trim(); // Title (3rd column)
                let value = row.find('td:eq(4)').text().trim(); // Value (4th column)
                let timeLimit = row.find('td:eq(5)').text().trim(); // Time Limit (5th column)
                let timeOption = row.find('td:eq(6)').text().trim(); // Time Option (6th column)
                let description = row.find('td:eq(7)').text().trim(); // Description (7th column)

                // Populate modal fields with extracted data
                $('#packageId').val(id); // Hidden input for ID
                $('#feature_type').val(feature_type); // Populate Feature Type dropdown
                $('#title').val(title); // Set Title input field
                $('#value').val(value); // Set Value input field
                $('#time_limit').val(timeLimit); // Set Time Limit input field
                $('#time_option').val(timeOption.toLowerCase()); // Set Time Option dropdown
                $('#description').val(description); // Set Description textarea

                // Update modal title
                $('#packageModalLabel').text('Edit Package Feature');

                // Show the modal
                $('#managePackageModal').modal('show');
            });

            // Delete Item
            $('.delete-button').click(function () {
                let row = $(this).closest('tr');
                let id = row.data('id'); // Get the package ID

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will permanently delete the package feature! All users associated with this package will be affected.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `${baseUrl}/${id}`, // Use the base URL and append the package ID
                            type: 'DELETE',
                            success: function (response) {
                                Swal.fire('Deleted!', response.success, 'success');
                                row.remove(); // Remove the deleted row from the table
                            },
                            error: function (xhr) {
                                let errorMessage = xhr.responseJSON?.error || 'Something went wrong.';
                                Swal.fire('Error!', errorMessage, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>

@endsection


