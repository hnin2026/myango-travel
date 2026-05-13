<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Add New Hotel</h2>
            <a href="{{ route('admin.hotels.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.hotels.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hotel Name</label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}"
                               placeholder="e.g. Sedona Hotel Yangon">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Category</label>
                        <select name="category"
                                class="form-select @error('category') is-invalid @enderror">
                            <option value="">Select category</option>
                            <option value="3-star" {{ old('category') == '3-star' ? 'selected' : '' }}>3 Star</option>
                            <option value="4-star" {{ old('category') == '4-star' ? 'selected' : '' }}>4 Star</option>
                            <option value="5-star" {{ old('category') == '5-star' ? 'selected' : '' }}>5 Star</option>
                        </select>
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Location</label>
                        <input type="text" name="location"
                               class="form-control"
                               value="{{ old('location') }}"
                               placeholder="e.g. Yangon, Myanmar">
                    </div>

                    {{-- Season Prices --}}
                    <div class="card mb-4">
                        <div class="card-header fw-bold">
                            <i class="bi bi-tag"></i> Upgrade Price by Season ($ per person)
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <span class="badge bg-success me-1">Low Season</span>
                                        Upgrade Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price_low"
                                               class="form-control"
                                               value="{{ old('price_low', 0) }}"
                                               min="0" step="0.01"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <span class="badge bg-warning text-dark me-1">Normal Season</span>
                                        Upgrade Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price_normal"
                                               class="form-control"
                                               value="{{ old('price_normal', 0) }}"
                                               min="0" step="0.01"
                                               placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">
                                        <span class="badge bg-danger me-1">Peak Season</span>
                                        Upgrade Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="price_peak"
                                               class="form-control"
                                               value="{{ old('price_peak', 0) }}"
                                               min="0" step="0.01"
                                               placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                Set $0 for 3-star hotels if no upgrade price applies.
                            </small>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Hotel
                        </button>
                        <a href="{{ route('admin.hotels.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>