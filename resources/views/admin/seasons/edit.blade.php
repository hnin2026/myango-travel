<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Edit Season Period</h2>
            <a href="{{ route('admin.season-periods.index') }}" class="btn btn-secondary">
               Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.season-periods.update', $seasonPeriod) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Season Name</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $seasonPeriod->name) }}"
                               placeholder="e.g. Peak Season 2026">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Season Type</label>
                        <select name="season"
                                class="form-select @error('season') is-invalid @enderror">
                            <option value="">Select season type</option>
                            <option value="low"    {{ old('season', $seasonPeriod->season) == 'low'    ? 'selected' : '' }}>Low Season</option>
                            <option value="normal" {{ old('season', $seasonPeriod->season) == 'normal' ? 'selected' : '' }}>Normal Season</option>
                            <option value="peak"   {{ old('season', $seasonPeriod->season) == 'peak'   ? 'selected' : '' }}>Peak Season</option>
                        </select>
                        @error('season')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" id="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', $seasonPeriod->start_date->format('Y-m-d')) }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', $seasonPeriod->end_date->format('Y-m-d')) }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                        <a href="{{ route('admin.season-periods.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            function updateMinEndDate() {
                if (startDateInput.value) {
                    endDateInput.min = startDateInput.value;
                } else {
                    endDateInput.removeAttribute('min');
                }
            }

            updateMinEndDate();
            startDateInput.addEventListener('change', updateMinEndDate);
        });
    </script>
</x-app-layout>