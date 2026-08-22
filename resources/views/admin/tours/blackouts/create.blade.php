<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Add Blackout Period for {{ $tour->title }}</h2>
            <a href="{{ route('admin.tours.travel-periods.index', $tour) }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.tours.blackouts.store', $tour) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Start Date</label>
                        <input type="date" name="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">End Date</label>
                        <input type="date" name="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Save
                        </button>
                        <a href="{{ route('admin.tours.travel-periods.index', $tour) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
