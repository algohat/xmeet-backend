@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row  align-items-center">
                            <div class="col-7">
                                <strong>All Packages</strong>
                            </div>

                            <div class="col-5">
                                <button class="btn btn-primary float-right" data-toggle="modal"
                                        data-target="#managePackageModal"><i class="icon-plus"></i> Add Package
                                </button>


                            </div>
                        </div>


                        <table class="table table-bordered zero-configuration">
                            <thead>
                            <tr class="bg-primary text-white">
                                <th>PID.</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Tag</th>
                                <th>Validity</th>
                                <th>Price & Duration</th>
                                <th>Feature</th>
                                <th>Is Paid</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody id="packageTable">
                            @foreach($packages as $key=>$package)
                                <tr data-id="{{ $package->id }}">
                                    <td>{{ $package->id }}</td>
                                    <td>{{ $package->name }}</td>
                                    <td>{{ $package->type }}</td>
                                    <td>
                                        @php
                                        if ($package->tag == 'Base') {
                                            echo '<span class="text-secondary">Base</span>';
                                        } elseif ($package->tag == 'Gold') {
                                            echo '<span class="text-warning">Gold</span>';
                                        } elseif ($package->tag == 'Platinum') {
                                            echo '<span class="text-success">Platinum</span>';
                                        } else {
                                            echo '<span class="text-secondary">'.$package->tag.'</span>';
                                        }
                                        @endphp
                                    </td>
                                    <td>{{ @$package->validity." ".@$package->validity_type }}</td>
                                    <td>
                                        Price: {{ $package->price }};
                                        Duration: {{ $package->duration }}
                                    </td>
                                    <td>
                                        <a href="{{route('admin.packages.feature.list', $package->id)}}" class="btn btn-sm btn-twitter" title="Features">
                                            <i class="fa fa-external-link"></i> View Features
                                        </a>
                                    </td>
                                    <td>
                                        <span class="btn btn-sm {{ $package->is_paid == 1 ? 'btn-outline-success' : 'btn-outline-danger' }}">
                                            {{ $package->is_paid == 1 ? 'YES' : 'NO' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch form-check-custom form-check-solid me-10">
                                            <input class="form-check-input h-20px w-30px" type="checkbox" value="" id="flexSwitch20x30 welcome_status_{{$package->id}}" {{$package?($package->status==1?'checked':''):''}}
                                            onclick="location.href='{{route('admin.packages.status',[$package->id])}}'"> <span>{{ $package->status == 1 ? 'Active' : 'Inactive'}}</span>
                                        </div>
                                    </td>

                                    <td>
                                        <button class="btn btn-sm btn-info edit-button" data-toggle="modal" title="Edit"
                                                data-target="#managePackageModal"><i class="fa fa-pencil-square-o"></i>
                                        </button>
                                        @if($package->id != 1)
                                        <button class="btn btn-sm btn-danger delete-button" title="Delete" data-id="{{ $package->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        @endif
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
                                            <label for="name">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="name" name="name" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="type">Type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="type" id="type" required>
                                                <option value="General">General</option>
                                                <option value="Monthly">Monthly</option>
                                                <option value="Yearly">Yearly</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="tag">Tag</label>
{{--                                            <input type="text" class="form-control" id="tag" name="tag">--}}
                                            <select class="form-control" name="tag" id="tag" required>
                                                <option value="Base">Base</option>
                                                <option value="Gold">Gold</option>
                                                <option value="Platinum">Platinum</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="validity">Validity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" id="validity" name="validity" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="validity_type">Validity type <span class="text-danger">*</span></label>
                                            <select class="form-control" name="validity_type" id="validity_type" required>
                                                <option value="week">Week</option>
                                                <option value="day">Day</option>
                                                <option value="month">Month</option>
                                                <option value="year">Year</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="price">Price</label>
                                            <input type="number" class="form-control" id="price" name="price">
                                        </div>

                                        <div class="form-group">
                                            <label for="duration">Duration (Total days)</label>
                                            <input type="number" class="form-control" id="duration" name="duration">
                                        </div>

                                        <div class="form-group">
                                            <label for="is_paid">Is Paid <span class="text-danger">*</span></label>
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
                let row = $(this).closest('tr');
                let id = row.data('id');
                let name = row.find('td:eq(1)').text().trim();
                let type = row.find('td:eq(2)').text().trim();
                let tag = row.find('td:eq(3)').text().trim();
                let validityText = row.find('td:eq(4)').text().trim().split(' '); // Split validity into value and type
                let validity = validityText[0];
                let validity_type = validityText[1];
                let priceAndDuration = row.find('td:eq(5)').text().trim(); // Extract price and duration
                let price = priceAndDuration.match(/Price:\s*([\d.]+)/i)?.[1] || '';
                let duration = priceAndDuration.match(/Duration:\s*([\d.]+)/i)?.[1] || '';
                let isPaidText = row.find('td:eq(7) span').text().trim(); // Extract isPaid text
                let isPaid = isPaidText === 'YES' ? "1" : "0";

                // Populate modal fields
                $('#packageId').val(id);
                $('#name').val(name);
                $('#type').val(type); // Select dropdown value
                $('#tag').val(tag); // Set the tag value
                $('#validity').val(validity);
                $('#validity_type').val(validity_type); // Select dropdown value
                $('#price').val(price);
                $('#duration').val(duration);
                $('#is_paid').val(isPaid); // Select dropdown value

                // Update modal title and show modal
                $('#packageModalLabel').text('Edit Package');
                $('#managePackageModal').modal('show');
            });

            // Delete Item
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


