<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Tour Management</h2>
            <a href="{{ route('admin.tours.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Tour
            </a>
        </div>
    </x-slot>

    <div class="container py-4">

        {{-- Success Message --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tours Table --}}
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tours as $tour)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $tour->title }}</td>
                                    <td>{{ $tour->location }}</td>
                                    <td>{{ $tour->duration_days }} days</td>
                                    <td>
                                        @if($tour->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                    <a href="{{ route('admin.tours.travel-periods.create', $tour) }}"
                                    class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-calendar"></i> Dates
                                    </a>
                                    <a href="{{ route('admin.tours.edit', $tour) }}"
                                    class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.tours.destroy', $tour) }}"
                                        method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this tour?')">
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
                                    <td colspan="7" class="text-center text-muted py-4">
                                        No tours found. <a href="{{ route('admin.tours.create') }}">Add your first tour!</a>
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