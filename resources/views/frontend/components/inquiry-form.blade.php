{{-- INQUIRY FORM --}}
<div class="inquiry-section">

    <h2 class="section-heading">Send an Inquiry</h2>

    <p style="color:#6b7280; margin-bottom:32px; font-size:15px;">
        Have questions about this tour? We'd love to hear from you.
    </p>

    @if(session('inquiry_success'))
        <div class="alert alert-success mb-4" style="border-radius:12px;">
            {{ session('inquiry_success') }}
        </div>
    @endif

    <form action="{{ route('inquiry.store') }}"
          method="POST"
          class="inquiry-form">

        @csrf

        {{-- Hidden Tour ID --}}
        <input type="hidden" name="tour_id" value="{{ $tour->id }}">

        <div class="row g-3">

            {{-- Tour --}}
            <div class="col-12">
                <label class="form-label-custom">Tour</label>

                <input type="text"
                       value="{{ $tour->title }}"
                       disabled
                       style="background:#f3f4f6; color:#6b7280;">
            </div>

            {{-- Name --}}
            <div class="col-md-6">
                <label class="form-label-custom">Name</label>

                <input type="text"
                       name="customer_name"
                       value="{{ old('customer_name') }}"
                       placeholder="Your name">

                @error('customer_name')
                    <div class="text-danger mt-1 small">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Nationality --}}
            <div class="col-md-6">
                <label class="form-label-custom">Nationality</label>

                <input type="text"
                       name="nationality"
                       value="{{ old('nationality') }}"
                       placeholder="e.g. Japanese">
            </div>

            {{-- Phone --}}
            <div class="col-md-6">
                <label class="form-label-custom">Phone</label>

                <input type="text"
                       name="phone"
                       value="{{ old('phone') }}"
                       placeholder="+95 9 123 456 789">
            </div>

            {{-- Email --}}
            <div class="col-md-6">
                <label class="form-label-custom">Email</label>

                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="john@email.com">

                @error('email')
                    <div class="text-danger mt-1 small">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Adults --}}
            <div class="col-md-6">
                <label class="form-label-custom">
                    Number of Adults
                </label>

                <input type="number"
                       name="number_of_adults"
                       min="1"
                       value="{{ old('number_of_adults', 1) }}">
            </div>

            {{-- Children --}}
            <div class="col-md-6">
                <label class="form-label-custom">
                    Number of Children
                </label>

                <input type="number"
                       name="number_of_children"
                       min="0"
                       value="{{ old('number_of_children', 0) }}">
            </div>

            {{-- Check-in --}}
            <div class="col-md-6">
                <label class="form-label-custom">
                    Check-in Date
                </label>

                <input type="date"
                       name="checkin_date"
                       value="{{ old('checkin_date') }}">
            </div>

            {{-- Check-out --}}
            <div class="col-md-6">
                <label class="form-label-custom">
                    Check-out Date
                </label>

                <input type="date"
                       name="checkout_date"
                       value="{{ old('checkout_date') }}">
            </div>

            {{-- Message --}}
            <div class="col-12">
                <label class="form-label-custom">
                    Message
                </label>

                <textarea name="message"
                          rows="5"
                          placeholder="Ask us anything about this tour..."
                          style="resize:vertical;">{{ old('message') }}</textarea>

                @error('message')
                    <div class="text-danger mt-1 small">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="col-12">
                <button type="submit"
                        class="inquiry-submit-btn">

                    <i class="bi bi-send me-2"></i>
                    Send Inquiry
                </button>
            </div>
        </div>
    </form>
</div>