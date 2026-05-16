<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Edit Dates for {{ $tour->title }}</h2>
            <a href="{{ route('admin.tours.dates.index', $tour) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tours.dates.update', [$tour, $date]) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Travel Date</label>
                        <input type="date" name="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', $date->date->format('Y-m-d')) }}">
                        @error('date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Seats</label>
                        <input type="number" name="total_seats"
                               class="form-control @error('total_seats') is-invalid @enderror"
                               value="{{ old('total_seats', $date->total_seats) }}"
                               min="1">
                        @error('total_seats')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Booked Seats</label>
                        <input type="number" class="form-control"
                               value="{{ $date->booked_seats }}" disabled>
                        <small class="text-muted">Booked seats cannot be edited manually.</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Date
                        </button>
                        <a href="{{ route('admin.tours.dates.index', $tour) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>