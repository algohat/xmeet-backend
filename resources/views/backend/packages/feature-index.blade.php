@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row align-items-center">
                            <div class="col-7">
                                <p><strong>Package Feature</strong></p>
                                <p>Package Name: <strong><span class="text-warning">{{ strtoupper($package->name) }}</span></strong> ;
                                    Type: <strong><span class="text-warning">{{ strtoupper($package->type) }}</span></strong> ;
                                    Tag: <strong><span class="text-danger">{{ strtoupper($package->tag) }}</span></strong>
                                </p>
                            </div>

                            <div class="col-5 d-flex justify-content-end">
                                <button class="btn btn-info mr-2" onclick="window.history.back();">
                                    <i class="icon-arrow-left-circle"></i> Package List
                                </button>
                                <button class="btn btn-primary" data-toggle="modal" data-target="#managePackageModal">
                                    <i class="icon-plus"></i> New Feature
                                </button>
                            </div>
                        </div>


                        <table class="table table-bordered zero-configuration">
                            <thead>
                            <tr class="bg-primary text-white">
                                <th>SL.</th>
                                <th>Package</th>
                                <th>Feature Type</th>
                                <th>Title</th>
                                <th>Value</th>
                                <th>Time Limit</th>
                                <th>Time option</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="packageTable">
                            @foreach($features as $key=>$item)
                                <tr data-id="{{ $item->id }}">
                                    <td>{{  $key+1  }}</td>
                                    <td>{{ $item->package_id }}</td>
                                    <td>{{ $item->feature_type }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->value }}</td>
                                    <td>{{ $item->time_limit }}</td>
                                    <td>{{ $item->time_option }}</td>
                                    <td>{{ $item->description }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-info edit-button" data-toggle="modal" title="Edit"
                                                data-target="#managePackageModal"><i class="fa fa-pencil-square-o"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-button" title="Delete" data-id="{{ $item->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="modal fade" id="managePackageModal" tabindex="-1" role="dialog"
                         aria-labelledby="packageModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="packageModalLabel">Add Package Feature</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="packageForm">
                                        <input type="hidden" id="packageId" name="id">
                                        <div class="form-group">
                                            <label for="package_id">Package Info</label>
                                            <select class="form-control" name="package_id" id="package_id" readonly>
                                                <option value="{{$package->id}}">{{$package->name}}</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="feature_type">Feature Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="feature_type" id="feature_type" required>
                                                <option value="" selected disabled>Select option</option>
                                                <option value="chat_credit">Chat Credit</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="title">Title</label>
                                            <input type="text" class="form-control" id="title" name="title">
                                        </div>

                                        <div class="form-group">
                                            <label for="value">Value</label>
                                            <input type="number" class="form-control" id="value" name="value">
                                        </div>

                                        <div class="form-group">
                                            <label for="time_limit">Time limit</label>
                                            <input type="number" class="form-control" id="time_limit" name="time_limit">
                                        </div>

                                        <div class="form-group">
                                            <label for="time_option">Time Option</label>
                                            <select class="form-control" name="time_option" id="time_option" required>
                                                <option value="">Unlimited</option>
                                                <option value="minute">Minute</option>
                                                <option value="hour">Hour</option>
                                                <option value="day">Day</option>
                                                <option value="week">Week</option>
                                                <option value="month">Month</option>
                                                <option value="year">Year</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description"></textarea>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="saveButton">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
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


