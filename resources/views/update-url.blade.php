@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div id="vue">
                    <add-url :token="'{{$apiToken}}'" :id="'{{$id}}'"></add-url>
                </div>
            </div>
        </div>
    </div>
@endsection
