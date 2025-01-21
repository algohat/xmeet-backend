@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row  align-items-center">
                            <div class="col-7">
                                <strong>Package Feature</strong>
                            </div>

                            <div class="col-5">
                                <button class="btn btn-primary float-right" data-toggle="modal"
                                        data-target="#managePackageModal">Add Feature
                                </button>


                            </div>
                        </div>


                        <table class="table table-bordered zero-configuration">
                            <thead>
                            <tr class="bg-primary text-white">
                                <th>SL.</th>
                                <th>Package</th>
                                <th>Feature Type</th>
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

                    <!-- Modal -->
                    <div class="modal fade" id="managePackageModal" tabindex="-1" role="dialog"
                         aria-labelledby="packageModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="packageModalLabel">Add Package</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="packageForm">
                                        <input type="hidden" id="packageId" name="id">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="type">Type</label>
                                            <select class="form-control" name="type" id="type" required>
                                                <option value="General">General</option>
                                                <option value="Monthly">Monthly</option>
                                                <option value="Yearly">Yearly</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="price">Price</label>
                                            <input type="number" class="form-control" id="price" name="price">
                                        </div>

                                        <div class="form-group">
                                            <label for="duration">Duration</label>
                                            <input type="number" class="form-control" id="duration" name="duration">
                                        </div>

                                        <div class="form-group">
                                            <label for="is_paid">Is Paid</label>
                                            <select class="form-control" name="is_paid" id="is_paid" required>
                                                <option value="1">Yes</option>
                                                <option value="0">No</option>
                                            </select>
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
            let baseUrl = `{{ url('admin/packages') }}`;
            // Add or Update Interest
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

            // Edit Interest
            $('.edit-button').click(function () {
                let row = $(this).closest('tr');
                let id = row.data('id');
                let name = row.find('td:eq(1)').text().trim();
                let type = row.find('td:eq(2)').text().trim();
                let price = row.find('td:eq(3)').text().trim();
                let duration = row.find('td:eq(4)').text().trim();
                let isPaid = row.find('td:eq(5)').text().trim() === 'YES' ? 1 : 0;

                $('#packageId').val(id);
                $('#name').val(name);
                $('#type').val(type);
                $('#price').val(price);
                $('#duration').val(duration);
                $('#is_paid').val(isPaid);

                $('#packageModalLabel').text('Edit Package');

                $('#managePackageModal').modal('show');
            });

            // Delete Interest
            $('.delete-button').click(function () {
                let row = $(this).closest('tr');
                let id = row.data('id'); // Get the package ID

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will permanently delete the package!',
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


