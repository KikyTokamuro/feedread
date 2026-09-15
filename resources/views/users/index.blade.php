@extends('layouts.main')

@section('content')
    @section('buttons')
        <a id="add-user-btn" href="{{ route('users.create') }}" class="btn btn-accent">
            <i class="bi bi-person-plus"></i> New account
        </a>
    @endsection

    <div class="page-header">
        <h2>Accounts</h2>
        <p class="page-header__meta">
            Every account keeps its own feeds. New accounts start empty until they add or import some.
        </p>
    </div>
    <hr>

    @if(session('generated_password'))
        @php($generated = session('generated_password'))
        <div class="alert alert-warning" role="alert">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <i class="bi bi-key fs-5"></i>
                <span>Password for <strong>{{ $generated['email'] }}</strong>:</span>
                <code class="user-select-all">{{ $generated['password'] }}</code>
            </div>
            <div class="small mt-1">This is shown only once. Copy it now and pass it on.</div>
        </div>
    @endif

    {{-- Validation errors are rendered once by the layout. --}}

    <div class="panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col" class="text-end">Feeds</th>
                    <th scope="col">Created</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge badge-soft">Administrator</span>
                            @else
                                <span class="badge badge-muted">User</span>
                            @endif
                        </td>
                        <td class="text-end">{{ $user->feeds_count }}</td>
                        <td><span class="text-secondary small">{{ $user->created_at?->format('j M Y') }}</span></td>
                        <td class="text-end">
                            @if(! auth()->user()->is($user))
                                <form action="{{ route('users.destroy', $user) }}" method="post"
                                      onsubmit="return confirm('Delete {{ $user->email }} and all of its feeds?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash3"></i> Delete
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-muted">Current account</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
