<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Customer Inquiries</h2>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">

                @if($inquiries->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Tour</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($inquiries as $inquiry)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <strong>{{ $inquiry->customer_name }}</strong>
                                        </td>

                                        <td>
                                            {{ $inquiry->tour->title ?? 'N/A' }}
                                        </td>

                                        <td>{{ $inquiry->email }}</td>

                                        <td>
                                            {{ $inquiry->phone ?? '-' }}
                                        </td>

                                        <td>
                                            @if($inquiry->status == 'new')
                                                <span class="badge bg-danger">
                                                    New
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    Replied
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $inquiry->created_at->format('d M Y') }}
                                        </td>

                                        <td>
                                            <a href="{{ route('admin.inquiries.show', $inquiry->id) }}"
                                               class="btn btn-sm btn-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                @else

                    <div class="text-center py-5">
                        <h5>No inquiries found</h5>
                        <p class="text-muted mb-0">
                            Customer inquiries will appear here.
                        </p>
                    </div>

                @endif

            </div>
        </div>

    </div>
</x-app-layout>