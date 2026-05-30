<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Edit Tour</h2>
            <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <form action="{{ route('admin.tours.update', $tour) }}" 
        method="POST" 
        enctype="multipart/form-data" 
        id="tour-form">
        @csrf
        @method('PUT')

            {{-- Tour Titles --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Tour Title</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Title (English)</label>
                            <input type="text" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $tour->title) }}"
                                   placeholder="e.g. Bagan Cultural Tour">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Title (Myanmar)</label>
                            <input type="text" name="title_mm"
                                   class="form-control"
                                   value="{{ old('title_mm', $tour->title_mm) }}"
                                   placeholder="ဥပမာ။ ဘဂါန် ယဉ်ကျေးမှုခရီးစဉ်">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3 Tabs --}}
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="tourTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#description">
                                <i class="bi bi-file-text"></i> Description
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#schedule">
                                <i class="bi bi-calendar3"></i> Schedule
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#additional">
                                <i class="bi bi-info-circle"></i> Additional Info
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content pt-3">

                        {{-- Description Tab --}}
                        <div class="tab-pane fade show active" id="description">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Description (English)</label>
                                <textarea name="description_en" id="description_en" class="tinymce">{{ old('description_en', $tour->description_en) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description (Myanmar)</label>
                                <textarea name="description_mm" id="description_mm" class="tinymce">{{ old('description_mm', $tour->description_mm) }}</textarea>
                            </div>
                        </div>

                        {{-- Schedule Tab --}}
                        <div class="tab-pane fade" id="schedule">
                            <div id="itinerary-container">
                                @forelse($itineraries as $index => $itinerary)
                                    <div class="itinerary-day card mb-3" data-day="{{ $index + 1 }}">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Day {{ $index + 1 }}</span>
                                            <button type="button" class="btn btn-sm btn-danger remove-day">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Description (English)</label>
                                                <textarea name="itineraries[{{ $index }}][description_en]"
                                                          class="form-control" rows="3">{{ $itinerary->description_en }}</textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Description (Myanmar)</label>
                                                <textarea name="itineraries[{{ $index }}][description_mm]"
                                                          class="form-control" rows="3">{{ $itinerary->description_mm }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="itinerary-day card mb-3" data-day="1">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Day 1</span>
                                            <button type="button" class="btn btn-sm btn-danger remove-day" style="display:none;">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label">Description (English)</label>
                                                <textarea name="itineraries[0][description_en]"
                                                          class="form-control" rows="3"
                                                          placeholder="Day 1 details in English"></textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">Description (Myanmar)</label>
                                                <textarea name="itineraries[0][description_mm]"
                                                          class="form-control" rows="3"
                                                          placeholder="နေ့ ၁ အသေးစိတ် မြန်မာဘာသာဖြင့်"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="add-day">
                                <i class="bi bi-plus-circle"></i> Add Day
                            </button>
                        </div>

                        {{-- Additional Info Tab --}}
                        <div class="tab-pane fade" id="additional">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Additional Info (English)</label>
                                <textarea name="additional_info_en" id="additional_info_en" class="tinymce">{{ old('additional_info_en', $tour->additional_info_en) }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Additional Info (Myanmar)</label>
                                <textarea name="additional_info_mm" id="additional_info_mm" class="tinymce">{{ old('additional_info_mm', $tour->additional_info_mm) }}</textarea>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Location, Duration & Base Price --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Tour Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Location</label>
                            <input type="text" name="location"
                                   class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location', $tour->location) }}"
                                   placeholder="e.g. Bagan, Myanmar">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Duration (Days)</label>
                            <input type="number" name="duration_days"
                                   class="form-control @error('duration_days') is-invalid @enderror"
                                   value="{{ old('duration_days', $tour->duration_days) }}"
                                   min="1">
                            @error('duration_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Base Price ($ per person)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="base_price"
                                       class="form-control @error('base_price') is-invalid @enderror"
                                       value="{{ old('base_price', $tour->base_price) }}"
                                       min="0" step="0.01">
                            </div>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Available Hotels --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Available Hotels</div>
                <div class="card-body">
                    @if($hotels->isEmpty())
                        <p class="text-muted">No hotels added yet.
                            <a href="{{ route('admin.hotels.create') }}">Add hotels first.</a>
                        </p>
                    @else
                        <div class="row">
                            @foreach($hotels as $hotel)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="hotels[]" value="{{ $hotel->id }}"
                                               id="hotel{{ $hotel->id }}"
                                               {{ in_array($hotel->id, old('hotels', $selectedHotels)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="hotel{{ $hotel->id }}">
                                            {{ $hotel->name }}
                                            <span class="badge bg-info">{{ $hotel->category }}</span>
                                            <small class="text-muted">
                                                Low: ${{ $hotel->getPriceForSeason('low') }} | 
                                                Normal: ${{ $hotel->getPriceForSeason('normal') }} | 
                                                Peak: ${{ $hotel->getPriceForSeason('peak') }}
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Existing Images --}}
            @if($tour->images->count() > 0)
                <div class="card mb-4">
                    <div class="card-header fw-bold">Current Images</div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($tour->images as $image)
                                <div class="col-md-3 mb-3 text-center">
                                    <img src="{{ asset('storage/' . $image->image_path) }}"
                                         class="img-fluid rounded mb-2"
                                         style="height: 120px; width: 100%; object-fit: cover;">
                                    <form action="{{ route('admin.tours.index') }}"
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Add New Images --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Add More Images</div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control"
                           accept="image/*" multiple>
                    <small class="text-muted">You can select multiple images. Accepted: JPG, PNG.</small>
                    <div id="image-preview" class="row mt-3"></div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" onclick="submitTourForm()">
                    Update
                </button>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>
    <script src="https://cdn.tiny.cloud/1/kepwie1vxizkqpicpc4g2arjl67ndtn5c2nmbjfe31hr1b0f/tinymce/6/tinymce.min.js"></script>
</x-app-layout>