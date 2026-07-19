@extends('frontend.layouts.app')

@section('title', 'Upload Payment Receipt - MyanGo Travel')

@section('content')
<div class="booking-payment-page">
    <div class="container">
        <div class="payment-wrapper">
            
            <div class="payment-header">
                <div class="payment-icon">
                    <i class="bi bi-credit-card"></i>
                </div>
                <h1 class="payment-title">Manual Payment Upload</h1>
                <p class="payment-subtitle">Please verify your booking details below and upload your payment receipt.</p>
            </div>

            <!-- Booking Details Card -->
            <div class="booking-payment-card">
                <div class="payment-card-header">
                    Booking Details
                </div>
                <div class="payment-detail-row">
                    <span>Booking Reference</span>
                    <strong>{{ $booking->ref_code }}</strong>
                </div>
                <div class="payment-detail-row">
                    <span>Tour Title</span>
                    <strong>{{ $booking->tour?->title }}</strong>
                </div>
                <div class="payment-detail-row">
                    <span>Travel Dates</span>
                    <strong>
                        {{ $booking->checkin_date }}
                        →
                        {{ $booking->checkout_date }}
                    </strong>
                </div>
                <div class="payment-detail-row">
                    <span>Total Amount</span>
                    <strong>USD {{ number_format($booking->total_price, 2) }}</strong>
                </div>
            </div>

            <!-- Receipt Upload Form -->
            <div class="upload-section">
                <h2 class="upload-title">
                    <i class="bi bi-file-earmark-arrow-up"></i> Upload Payment Receipt
                </h2>
                
                <form action="{{ route('payment.upload', $booking->cancellation_token) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="file-dropzone" onclick="document.getElementById('receipt').click();">
                        <div class="dropzone-icon">
                            <i class="bi bi-cloud-upload"></i>
                        </div>
                        <div class="file-input-wrapper">
                            <input type="file" name="receipt" id="receipt" accept=".jpg,.jpeg,.png,.pdf" required class="form-control" style="display: none;" onchange="updateFileName(this);">
                            <span id="file-label" class="btn btn-outline-secondary btn-sm">Choose File</span>
                        </div>
                        <div id="file-chosen" class="mt-2 text-muted fw-bold">No file chosen</div>
                        <div class="upload-instructions">
                            Accepted file formats: <strong>JPG, JPEG, PNG, PDF</strong><br>
                            Maximum file size: <strong>5MB</strong>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn" disabled>
                        Submit Receipt
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateFileName(input) {
        const label = document.getElementById('file-chosen');
        const submitBtn = document.querySelector('.submit-btn');
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const fileSize = file.size / 1024 / 1024; // size in MB
            
            label.textContent = file.name;
            
            if (fileSize > 5) {
                alert('File size exceeds the 5MB limit. Please choose a smaller file.');
                input.value = '';
                label.textContent = 'No file chosen';
                submitBtn.disabled = true;
            } else {
                submitBtn.disabled = false;
            }
        } else {
            label.textContent = 'No file chosen';
            submitBtn.disabled = true;
        }
    }
</script>
@endpush
