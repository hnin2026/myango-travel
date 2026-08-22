<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Tour Management</h1>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('admin.tours.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search tour..." 
                           value="{{ request('search') }}"
                           style="width: 250px; height: 40px;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Search" style="height: 40px; width: 40px;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <a href="{{ route('admin.tours.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Add New Tour" title="Add New Tour" style="height: 40px; width: 40px;">
                    <i class="bi bi-plus-circle"></i>
                </a>
            </div>
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
                                    <td>{{ $loop->iteration + ($tours->firstItem() - 1) }}</td>
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
                                    <a href="{{ route('admin.tours.travel-periods.index', $tour) }}"
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
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No tours found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $tours->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>