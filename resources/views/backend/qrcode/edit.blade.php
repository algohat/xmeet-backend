@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Edit QR Code</h4><br>


                        <div class="basic-form">

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif


                            <form method="post" action="{{ route('qrcode.update',$data->id) }}">
                                @csrf



                                <div class="form-group col-md-6">
                                    <label>Point:</label>
                                    <input type="number" step="0.01" min="0" name="point"
                                           class="form-control" autocomplete="off" required=""
                                           placeholder="***" value="{{ $data->point  }}">
                                <br>
                                    <center> <button type="submit" class="btn btn-primary">Update Now</button></center>
                                </div>






                            </form>


                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection


