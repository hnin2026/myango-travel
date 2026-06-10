<x-app-layout>

    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Inquiry Detail</h2>

            <a href="{{ route('admin.inquiries.index') }}"
               class="btn btn-secondary">
                Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">

        <div class="card">
            <div class="card-body">

                <div class="mb-3">
                    <strong>Tour:</strong>
                    {{ $inquiry->tour->title ?? '-' }}
                </div>

                <div class="mb-3">
                    <strong>Name:</strong>
                    {{ $inquiry->customer_name }}
                </div>

                <div class="mb-3">
                    <strong>Nationality:</strong>
                    {{ $inquiry->nationality }}
                </div>

                <div class="mb-3">
                    <strong>Email:</strong>
                    {{ $inquiry->email }}
                </div>

                <div class="mb-3">
                    <strong>Phone:</strong>
                    {{ $inquiry->phone }}
                </div>

                <div class="mb-3">
                    <strong>Adults:</strong>
                    {{ $inquiry->number_of_adults }}
                </div>

                <div class="mb-3">
                    <strong>Children:</strong>
                    {{ $inquiry->number_of_children }}
                </div>

                <div class="mb-3">
                    <strong>Check-in:</strong>
                    {{ $inquiry->checkin_date }}
                </div>

                <div class="mb-3">
                    <strong>Check-out:</strong>
                    {{ $inquiry->checkout_date }}
                </div>

                <div class="mb-3">
                    <strong>Message:</strong>

                    <div class="border rounded p-3 mt-2 bg-light">
                        {{ $inquiry->message }}
                    </div>
                </div>

            </div>
        </div>

    </div>

</x-app-layout>