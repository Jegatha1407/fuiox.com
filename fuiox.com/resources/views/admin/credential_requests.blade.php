@extends('layouts.admin')

@section('title', 'Credential Requests')
@section('page_title', 'Credential Update Requests')

@section('content')

    <div class="card shadow-sm rounded-3">
        <div class="card-body">
            <h5 class="fw-bold mb-3">
                <i class="bi bi-key-fill me-2 text-warning"></i>All Requests
            </h5>

            @if($requests->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                    No credential update requests yet.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Reason</th>
                            <th class="d-none d-md-table-cell">Status</th>
                            <th class="d-none d-sm-table-cell">Requested</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $req->user->name }}</div>
                                <div class="text-muted small">{{ $req->user->email }}</div>
                                <div class="text-muted small">{{ $req->user->organisation }}</div>
                            </td>
                            <td>
                                <span class="text-muted fst-italic small">
                                    {{ $req->reason ?: 'No reason provided' }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">
                                @if($req->status === 'pending')
                                    <span class="badge rounded-pill bg-warning text-dark">⏳ Pending</span>
                                @elseif($req->status === 'accepted')
                                    <span class="badge rounded-pill bg-success">✓ Accepted</span>
                                @else
                                    <span class="badge rounded-pill bg-danger">✕ Rejected</span>
                                @endif
                            </td>
                            <td class="d-none d-sm-table-cell text-muted small">
                                {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y, h:i A') }}
                            </td>
                            <td>
                                @if($req->status === 'pending')
                                <div class="d-flex gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('admin.credential.accept', $req->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2">
                                            <i class="bi bi-check-lg"></i>
                                            <span class="d-none d-md-inline ms-1">Accept</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.credential.reject', $req->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                            <i class="bi bi-x-lg"></i>
                                            <span class="d-none d-md-inline ms-1">Reject</span>
                                        </button>
                                    </form>
                                </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

@endsection