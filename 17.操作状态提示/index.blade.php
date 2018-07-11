@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
@endif

<table class="table">
    <thead>
        <tr>
            <th colspan="5">
                <form action="{{ route('website.create') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="@lang('messages.form.website.name_placeholder')">
                        <input type="text" name="uri" class="form-control" placeholder="@lang('messages.form.website.uri_placeholder')">

                        <button type="submit" class="btn btn-primary float-right">@lang('messages.ui.add')</button>
                    </div>
                </form>
            </th>
        </tr>
    </thead>
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">@lang('messages.website.name')</th>
            <th scope="col">@lang('messages.website.uri')</th>
            <th scope="col">@lang('messages.website.status')</th>
            <th scope="col">@lang('messages.ui.actions')</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($websites as $website)
            <tr>
                <th scope="row">{{ $website->id }}</th>
                <td><a href="{{ route('website.show', ['id' => $website->id]) }}">{{ $website->name }}</a></td>
                <td>{{ $website->uri }}</td>
                <td>
                    <span class="badge @if ($website->status === 'success')badge-success @elseif ($website->status === 'new') badge-primary @elseif ($website->status === 'running') badge-secondary @endif">{{ $website->status }}</span>
                </td>
                <td>
                    <div class="btn-toolbar">
                        <form action="{{ route('website.crawl', ['id' => $website->id]) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-info btn-sm" @if ($website->status === 'running') disabled @endif>@lang('messages.ui.start')</button>
                        </form>
                        &nbsp;
                        <form action="{{ route('website.delete', ['id' => $website->id]) }}" method="post">
                            <input type="hidden" name="_method" value="DELETE">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">@lang('messages.ui.delete')</button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
{{ $websites->links() }}
@endsection
