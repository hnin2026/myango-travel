<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Hotel Management</h1>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('admin.hotels.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Search hotel..." 
                           value="{{ request('search') }}"
                           style="width: 250px; height: 40px;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Search" style="height: 40px; width: 40px;">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
                <a href="{{ route('admin.hotels.create') }}" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Add New Hotel" title="Add New Hotel" style="height: 40px; width: 40px;">
                    <i class="bi bi-plus-circle"></i>
                </a>
            </div>
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
                                <th>Hotel Name</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotels as $hotel)
                                <tr>
                                    <td>{{ $loop->iteration + ($hotels->firstItem() - 1) }}</td>
                                    <td>{{ $hotel->name }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $hotel->category }}</span>
                                    </td>
                                    <td>{{ $hotel->location ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.hotels.edit', $hotel) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.hotels.destroy', $hotel) }}" 
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
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No hotels found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $hotels->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>