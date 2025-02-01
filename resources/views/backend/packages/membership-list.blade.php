@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <!-- Filter Section -->
                        <form action="{{ url()->current() }}" method="get" class="mb-4">
                            <div class="row align-items-center g-3 d-flex justify-content-end">
                                <!-- Search Input -->
                                <div class="col-md-3">
                                    <input type="text" name="search" value="{{ $search }}"
                                           class="form-control form-control-solid"
                                           placeholder="Search by keyword" />
                                </div>

                                <!-- Status Select -->
                                <div class="col-md-3">
                                    <select class="form-control"
                                            name="package_id" aria-label="Filter by status">
                                        <option value="">Select Packages</option>
                                        @foreach($packages as $package)
                                            <option value="{{ $package->id }}"
                                                    {{ isset($_GET['package_id']) && $_GET['package_id'] == $package->id ? 'selected' : '' }}>
                                                {{ $package->name }} - {{ $package->type }} - {{ $package->tag }} - {{ $package->validity }} {{ $package->validity_type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Select -->
                                <div class="col-md-3">
                                    <select class="form-control"
                                            name="status" aria-label="Filter by status">
                                        <option value="">Select Status</option>
                                        <option value="1" {{ isset($_GET['status']) && $_GET['status'] == 1 ? 'selected' : '' }}>Pending</option>
                                        <option value="2" {{ isset($_GET['status']) && $_GET['status'] == 2 ? 'selected' : '' }}>Active</option>
                                        <option value="3" {{ isset($_GET['status']) && $_GET['status'] == 3 ? 'selected' : '' }}>Expired</option>
                                        <option value="4" {{ isset($_GET['status']) && $_GET['status'] == 4 ? 'selected' : '' }}>Cancelled</option>
                                        <option value="5" {{ isset($_GET['status']) && $_GET['status'] == 5 ? 'selected' : '' }}>Inactive</option>
                                        <option value="6" {{ isset($_GET['status']) && $_GET['status'] == 6 ? 'selected' : '' }}>Renewal</option>
                                    </select>
                                </div>

                                <!-- Buttons -->
                                <div class="col-md-3 text-md-end">
                                    <button type="submit" class="btn btn-outline-info me-2">Apply</button>
                                    <a href="{{ url()->current() }}" class="btn btn-outline-danger">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Table -->
                        <table class="table table-bordered zero-configuration">
                            <thead>
                            <tr class="bg-primary text-white">
                                <th>PID</th>
                                <th>User Info</th>
                                <th>Package Info</th>
                                <th>Price</th>
                                <th>Medium</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Status1</th>
                                <th>Invoice</th>
                            </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>
                                        @if(!empty($item->user))
                                            <div class="d-flex flex-column">
                                                <a href="#" class="text-gray-800 text-hover-primary mb-1">
                                                    {{ $item->user->name }}
                                                </a>
                                                <span>{{ $item->user->email ?? 'N/A' }}</span>
                                                <span>{{ $item->user->phone }}</span>
                                                <span class="text-muted">UID: {{ $item->user->id }}</span>
                                            </div>
                                        @else
                                            <span class="badge bg-danger">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        Name: {{ $item->package->name }} <br />
                                        Validity: {{ $item->package->validity . ' ' . $item->package->validity_type }} <br />
                                        Type: {{ $item->package->type }}; Tag: {{ $item->package->tag ?? 'N/A' }}
                                    </td>
                                    <td>${{ $item->price ?? 'N/A' }} USD</td>
                                    <td>{{ strtoupper($item->payment_medium) ?? 'N/A' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->start_time)->format('d F Y') }}<br>
                                        {{ \Carbon\Carbon::parse($item->start_time)->format('H:i:s') }}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->end_time)->format('d F Y') }}<br>
                                        {{ \Carbon\Carbon::parse($item->end_time)->format('H:i:s') }}
                                    </td>
                                    <td>
                                        @switch($item->status)
                                            @case(1) <span class="text-info">Pending</span> @break
                                            @case(2) <span class="text-success">Active</span> @break
                                            @case(3) <span class="text-danger">Expired</span> @break
                                            @case(4) <span class="text-warning">Cancelled</span> @break
                                            @case(5) <span class="text-secondary">Inactive</span> @break
                                            @case(6) <span class="text-primary">Renewed</span> @break
                                            @default <span class="text-secondary">No Status</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if(!empty($item->invoice_path))
                                            <a href="{{ asset('storage/'.$item->invoice_path) }}" download>
                                                <i class="fa fa-download"></i> Download
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="mt-4 d-flex justify-content-center">
                            {!! $items->links() !!}
                        </div>
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


