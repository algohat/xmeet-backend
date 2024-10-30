@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-0">

                        <div class="row p-3 align-items-center">
                            <div class="col-7">
                                <strong>All Users Information</strong>
                            </div>


                        <div class="table-responsive">
                            <table class="table table-bordered zero-configuration">
                                <thead>
                                <tr class="bg-primary text-white">
                                    <th>SL.</th>
                                    <th>Personal Info.</th>
                                    <th>Post Code</th>
                                    <th>Email</th>
                                    <th>Interest</th>
                                    <th>Join Date</th>
                                    <th>Action</th>
                                </tr>
                                </thead>



                                @if(isset($users))
                                    @foreach($users as $key=>$item)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <th>
                                                Name: {{ $item->name }}<br>
                                                Phone: {{ $item->phone }}<br>
                                                Age: {{ $item->age }}<br>

                                            </th>
                                            <td>{{ $item->post_code }}</td>
                                            <td>{{ $item->email }}</td>
                                            <th>
                                                @foreach(explode(',', $item->interest) as $interest)
                                                    <button class="btn btn-primary btn-sm">{{ trim($interest) }}</button>
                                                @endforeach
                                            </th>
                                            <td>{{ $item->created_at }}</td>

                                            <td>
                                                <a  class="btn btn-danger btn-sm text-white" onclick="return confirmDelete({{ $item->id  }})">
                                                    <i class="fa fa-trash"></i>
                                                </a>

                                                <form id="delete-form-{{ $item->id }}"
                                                      action="{{ route('user.delete', $item->id) }}" method="POST"
                                                      style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>


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


