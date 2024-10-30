@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-0">

                        <div class="row p-3 align-items-center">
                            <div class="col-7">
                                <strong>All QR Code Generate</strong>
                            </div>

                            <div class="col-5">
                                <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                                        data-target="#basicModal">Add New
                                </button>
                                <form method="post" action="{{ route('qrcode.store') }}">
                                    @csrf
                                    <div class="modal fade" id="basicModal">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-weight-bold text-primary">New QR Code Generate</h5>
                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">

                                                    <div class="form-group col-md-12">
                                                        <label>Point:</label>
                                                        <input type="number" step="0.01" min="0" name="point" class="form-control" autocomplete="off" required="" placeholder="***">
                                                    </div>


                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary text-white"
                                                            data-dismiss="modal">Close
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">Save Now</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </form>


                            </div>
                        </div>


                        <div class="table-responsive">
                            <table class="table table-bordered zero-configuration">
                                <thead>
                                <tr class="bg-primary text-white">
                                    <th>SL.</th>
                                    <th>QR Code</th>
                                    <th>Points</th>
                                    <th>Added By</th>
                                    <th>Action</th>
                                </tr>
                                </thead>



                                @if(isset($data))
                                    @foreach($data as $key=>$item)

                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>
                                                 <a href="data:image/png;base64,{{DNS2D::getBarcodePNG($item->code, 'QRCODE')}}" download="{{ $item->code }}" title="Download"><img src="data:image/png;base64,{{DNS2D::getBarcodePNG($item->code, 'QRCODE')}}" class="bg-white border" alt="barcode" /></a>


                                            </td>
                                            <th>{{ $item->point }}</th>
                                            <td>{{ $item->user->name }}</td>

                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-primary dropdown-toggle"
                                                            type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                       Option
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        <a class="dropdown-item" href="{{ route("qrcode.edit",$item->id) }}">Edit</a>

                                                        <a  class="dropdown-item" onclick="return confirmDelete({{ $item->id  }})">Delete</a>

                                                        <form id="delete-form-{{ $item->id }}"
                                                              action="{{ route('qrcode.delete', $item->id) }}" method="POST"
                                                              style="display: none;">
                                                            @csrf
                                                            @method('DELETE')
                                                        </form>


                                                    </div>
                                                </div>

                                            </td>

                                        </tr>


                                @endforeach
                                @endif

                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    </div>

@endsection


