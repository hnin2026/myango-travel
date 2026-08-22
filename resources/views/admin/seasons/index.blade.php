<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Season Periods</h1>
            </div>
            <a href="{{ route('admin.season-periods.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Add Season Period" title="Add Season Period" style="height: 40px; width: 40px;">
                <i class="bi bi-plus-circle"></i>
            </a>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Name</th>
                                <th>Season</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($seasons as $season)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $season->name }}</td>
                                    <td>
                                        @if($season->season === 'peak')
                                            <span class="badge bg-danger">Peak</span>
                                        @elseif($season->season === 'normal')
                                            <span class="badge bg-warning text-dark">Normal</span>
                                        @else
                                            <span class="badge bg-success">Low</span>
                                        @endif
                                    </td>
                                    <td>{{ $season->start_date->format('d M Y') }}</td>
                                    <td>{{ $season->end_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.season-periods.edit', $season) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.season-periods.destroy', $season) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No season periods found.
                                        <a href="{{ route('admin.season-periods.create') }}">Add your first season!</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>