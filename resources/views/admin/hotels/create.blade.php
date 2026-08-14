<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h1 class="page-title mb-1">
                    Add New Hotel
                </h1>

                <p class="text-muted mb-0">
                    Create hotel upgrade pricing for tour packages
                </p>
            </div>

            <a href="{{ route('admin.hotels.index') }}"
               class="btn btn-secondary border rounded-pill px-4 ms-auto">
                Back
            </a>
        </div>
    </x-slot>

    <div class="admin-card">

        <form action="{{ route('admin.hotels.store') }}" method="POST">
            @csrf

            {{-- BASIC INFORMATION --}}
            <div class="mb-5">

                <div class="d-flex align-items-center mb-4">
                    <div class="section-icon me-3">
                        <i class="bi bi-building"></i>
                    </div>

                    <div>
                        <h4 class="mb-1 fw-bold">
                            Hotel Information
                        </h4>

                        <p class="text-muted mb-0">
                            Basic hotel details
                        </p>
                    </div>
                </div>

                <div class="row g-4">

                    {{-- HOTEL NAME --}}
                    <div class="col-lg-6">

                        <label class="form-label-custom">
                            Hotel Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control form-control-custom @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. Sedona Hotel Yangon"
                        >

                        @error('name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- CATEGORY --}}
                    <div class="col-lg-6">

                        <label class="form-label-custom">
                            Hotel Category
                        </label>

                        <select
                            name="category"
                            class="form-select form-select-custom @error('category') is-invalid @enderror"
                        >
                            <option value="">
                                Select category
                            </option>

                            <option value="3-star"
                                {{ old('category') == '3-star' ? 'selected' : '' }}>
                                ★★★ 3 Star
                            </option>

                            <option value="4-star"
                                {{ old('category') == '4-star' ? 'selected' : '' }}>
                                ★★★★ 4 Star
                            </option>

                            <option value="5-star"
                                {{ old('category') == '5-star' ? 'selected' : '' }}>
                                ★★★★★ 5 Star
                            </option>
                        </select>

                        @error('category')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- LOCATION --}}
                    <div class="col-12">

                        <label class="form-label-custom">
                            Location
                        </label>

                        <div x-data="locationAutocomplete({
                            initialValue: {{ json_encode(old('location')) }},
                            suggestions: {{ json_encode($existingLocations) }},
                            allowNew: true
                        })" class="autocomplete-wrapper">
                            <input
                                type="text"
                                name="location"
                                x-model="search"
                                @focus="open = true"
                                @click.away="open = false"
                                @keydown.escape="open = false"
                                class="form-control form-control-custom @error('location') is-invalid @enderror"
                                placeholder="e.g. Yangon, Myanmar"
                                autocomplete="off"
                            >

                            @error('location')
                                <div class="invalid-feedback d-block mt-2">
                                    {{ $message }}
                                </div>
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
                                    No matching location found.
                                </div>
                                <div x-show="search.trim() !== '' && !hasExactMatch"
                                     @click="addNewLocation()"
                                     class="autocomplete-add-new">
                                    ➕ Add "<span x-text="search"></span>" as a new location?
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- SEASON PRICING --}}
            <div class="mb-5">

                <div class="d-flex align-items-center mb-4">
                    <div class="section-icon me-3">
                        <i class="bi bi-currency-dollar"></i>
                    </div>

                    <div>
                        <h4 class="mb-1 fw-bold">
                            Seasonal Upgrade Pricing
                        </h4>

                        <p class="text-muted mb-0">
                            Additional hotel price per person
                        </p>
                    </div>
                </div>

                <div class="row g-4">

                    {{-- LOW SEASON --}}
                    <div class="col-lg-4">

                        <div class="season-card low-season">

                            <div class="season-badge success">
                                <i class="bi bi-circle-fill"></i>
                                Low Season
                            </div>

                            <div class="mt-4">

                                <label class="form-label-custom">
                                    Upgrade Price
                                </label>

                                <div class="input-group custom-group">

                                    <span class="input-group-text">
                                        $
                                    </span>

                                    <input
                                        type="number"
                                        name="price_low"
                                        class="form-control form-control-custom"
                                        value="{{ old('price_low', 0) }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- NORMAL SEASON --}}
                    <div class="col-lg-4">

                        <div class="season-card normal-season">

                            <div class="season-badge warning">
                                <i class="bi bi-circle-fill"></i>
                                Normal Season
                            </div>

                            <div class="mt-4">

                                <label class="form-label-custom">
                                    Upgrade Price
                                </label>

                                <div class="input-group custom-group">

                                    <span class="input-group-text">
                                        $
                                    </span>

                                    <input
                                        type="number"
                                        name="price_normal"
                                        class="form-control form-control-custom"
                                        value="{{ old('price_normal', 0) }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- PEAK SEASON --}}
                    <div class="col-lg-4">

                        <div class="season-card peak-season">

                            <div class="season-badge danger">
                                <i class="bi bi-circle-fill"></i>
                                Peak Season
                            </div>

                            <div class="mt-4">

                                <label class="form-label-custom">
                                    Upgrade Price
                                </label>

                                <div class="input-group custom-group">

                                    <span class="input-group-text">
                                        $
                                    </span>

                                    <input
                                        type="number"
                                        name="price_peak"
                                        class="form-control form-control-custom"
                                        value="{{ old('price_peak', 0) }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="0.00"
                                    >

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- NOTE --}}
                <div class="pricing-note mt-4">

                    <div class="d-flex align-items-start">

                        <i class="bi bi-info-circle-fill me-3"></i>

                        <div>
                            <strong>
                                Pricing Note
                            </strong>

                            <div class="mt-1">
                                Set $0 for hotels without additional upgrade pricing.
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex gap-3 flex-wrap">

                <button type="submit" class="btn btn-primary">Save</button>

                <a href="{{ route('admin.hotels.index') }}"
                   class="btn btn-secondary">Cancel</a>

            </div>

        </form>

    </div>
</x-app-layout>