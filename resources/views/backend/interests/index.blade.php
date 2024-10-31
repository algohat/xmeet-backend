@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row  align-items-center">
                            <div class="col-7">
                                <strong>All Insterests</strong>
                            </div>

                            <div class="col-5">
                                <button class="btn btn-primary float-right" data-toggle="modal" data-target="#interestModal">Add Interest</button>


                            </div>
                        </div>




                            <table class="table table-bordered zero-configuration">
                                <thead>
                                <tr class="bg-primary text-white">
                                    <th>SL.</th>
                                    <th width="70%">Name</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody id="interestTable">
                                @foreach($interests as $key=>$interest)
                                    <tr data-id="{{ $interest->id }}">
                                        <td>{{  $key+1  }}</td>
                                        <td>{{ $interest->name }}</td>
                                        <td>
                                            <button class="btn btn-info edit-button" data-toggle="modal" data-target="#interestModal">Edit</button>
                                            <button class="btn btn-danger delete-button">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="interestModal" tabindex="-1" role="dialog" aria-labelledby="interestModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="interestModalLabel">Add Interest</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="interestForm">
                                            <input type="hidden" id="interestId" name="id">
                                            <div class="form-group">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
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
            // Add or Update Interest
            $('#saveButton').click(function () {
                let formData = $('#interestForm').serialize();
                let interestId = $('#interestId').val();
                let url = interestId ? `/admin/interests/${interestId}` : '/admin/interests';
                let method = interestId ? 'PUT' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function (response) {
                        Swal.fire('Success!', response.success, 'success');
                        location.reload();
                    },
                    error: function (response) {
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                    }
                });
            });

            // Edit Interest
            $('.edit-button').click(function () {
                let row = $(this).closest('tr');
                let id = row.data('id');
                let name = row.find('td:eq(1)').text();

                $('#interestId').val(id);
                $('#name').val(name);
                $('#interestModalLabel').text('Edit Interest');
            });

            // Delete Interest
            $('.delete-button').click(function () {
                let row = $(this).closest('tr');
                let id = row.data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will delete the interest!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/interests/${id}`,
                            type: 'DELETE',
                            success: function (response) {

                                row.remove();
                            },
                            error: function (response) {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>


@endsection


