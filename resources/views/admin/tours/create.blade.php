<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Add New Tour</h2>
            <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">
        <form action="{{ route('admin.tours.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Tour Titles --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Tour Title</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Title (English)</label>
                            <input type="text" name="title" 
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title') }}" 
                                   placeholder="e.g. Bagan Cultural Tour">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Title (Myanmar)</label>
                            <input type="text" name="title_mm" 
                                   class="form-control"
                                   value="{{ old('title_mm') }}" 
                                   placeholder="ဥပမာ။ ပုဂံ ယဉ်ကျေးမှု ခရီးစဉ်">
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
                                <textarea name="description_en" id="description_en" class="tinymce">{{ old('description_en') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description (Myanmar)</label>
                                <textarea name="description_mm" id="description_mm" class="tinymce">{{ old('description_mm') }}</textarea>
                            </div>
                        </div>

                        {{-- Schedule Tab --}}
                        <div class="tab-pane fade" id="schedule">
                            <div id="itinerary-container">
                                <div class="itinerary-day card mb-3" data-day="1">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <span class="fw-bold">Day 1</span>
                                        <button type="button" class="btn btn-sm btn-danger remove-day" style="display:none;">
                                            <i class="bi bi-trash"></i> Remove
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Tour Itinerary (English)</label>
                                            <textarea id="itinerary_en_1" name="itineraries[0][description_en]" 
                                                    class="form-control tinymce" rows="3"
                                                    placeholder="Day 1 details in English"></textarea>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label">Tour Itinerary (Myanmar)</label>
                                            <textarea id="itinerary_mm_1" name="itineraries[0][description_mm]" 
                                                    class="form-control tinymce" rows="3"
                                                    placeholder="Day 1 details in Myanmar"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary" id="add-day">
                                <i class="bi bi-plus-circle"></i> Add Day
                            </button>
                        </div>

                        {{-- Additional Info Tab --}}
                        <div class="tab-pane fade" id="additional">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Additional Info (English)</label>
                                <textarea name="additional_info_en" id="additional_info_en" class="tinymce">{{ old('additional_info_en') }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Additional Info (Myanmar)</label>
                                <textarea name="additional_info_mm" id="additional_info_mm" class="tinymce">{{ old('additional_info_mm') }}</textarea>
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
                            <div x-data="locationAutocomplete({
                                initialValue: {{ json_encode(old('location')) }},
                                suggestions: {{ json_encode($existingLocations) }},
                                allowNew: false
                            })" class="autocomplete-wrapper">
                                <input
                                    type="text"
                                    name="location"
                                    x-model="search"
                                    @focus="open = true"
                                    @click.away="open = false"
                                    @keydown.escape="open = false"
                                    class="form-control @error('location') is-invalid @enderror"
                                    placeholder="e.g. Bagan, Myanmar"
                                    autocomplete="off"
                                >
                                @error('location')
                                    <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror

                                <div x-show="open && (filteredSuggestions.length > 0 || search.trim() !== '')"
                                     class="autocomplete-dropdown"
                                     x-cloak>
                                    <template x-for="suggestion in filteredSuggestions" :key="suggestion">
                                        <div @click="selectSuggestion(suggestion)" class="autocomplete-item">
                                            <span class="me-2">📍</span>
                                            <span x-text="suggestion"></span>
                                        </div>
                                    </template>
                                    <div x-show="filteredSuggestions.length === 0 && search.trim() !== ''" class="autocomplete-no-match">
                                        <div>No existing location found.</div>
                                        <div class="text-danger small mt-1">Please create a hotel with this location first.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Duration (Days)</label>
                            <input type="number" name="duration_days"
                                class="form-control @error('duration_days') is-invalid @enderror"
                                value="{{ old('duration_days') }}"
                                min="1" placeholder="e.g. 3">
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
                                    value="{{ old('base_price', 0) }}"
                                    min="0" step="0.01"
                                    placeholder="e.g. 400.00">
                            </div>
                            @error('base_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input @error('status') is-invalid @enderror" 
                                           type="radio" 
                                           name="status" 
                                           id="status_active" 
                                           value="active" 
                                           {{ old('status', 'active') === 'active' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_active">Active</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input @error('status') is-invalid @enderror" 
                                           type="radio" 
                                           name="status" 
                                           id="status_inactive" 
                                           value="inactive" 
                                           {{ old('status') === 'inactive' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_inactive">Inactive</label>
                                </div>
                            </div>
                            @error('status')
                                <div class="text-danger small mt-1 d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Available Hotels --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Available Hotels</div>
                <div class="card-body" id="hotels-container">
                    <p class="text-muted">Please select a location first.</p>
                </div>
            </div>

            {{-- Images --}}
            <div class="card mb-4">
                <div class="card-header fw-bold">Tour Images</div>
                <div class="card-body">
                    <input type="file" name="images[]" class="form-control" 
                           accept="image/*" multiple>
                    <small class="text-muted">You can select multiple images. Accepted: JPG, PNG.</small>
                    <div id="image-preview" class="row mt-3"></div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" onclick="submitTourForm()">Save</button>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    {{-- TinyMCE --}}
    <script src="https://cdn.tiny.cloud/1/kepwie1vxizkqpicpc4g2arjl67ndtn5c2nmbjfe31hr1b0f/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <script>
        window.selectedHotels = @json(old('hotels', [])) ? @json(old('hotels', [])).map(id => parseInt(id)) : [];
        document.addEventListener('DOMContentLoaded', () => {
            const initialLocation = '{{ old('location') }}';
            if (initialLocation) {
                window.fetchHotels(initialLocation);
            }
        });
    </script>
</x-app-layout>