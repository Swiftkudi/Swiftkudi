@extends('layouts.app')

@section('title', 'Service Details')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.professional-services') }}">Professional Services</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($service->title, 30) }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Service Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Service Information</h5>
                    @php
                        $statusClasses = [
                            'pending' => 'bg-warning',
                            'active' => 'bg-success',
                            'rejected' => 'bg-danger',
                            'paused' => 'bg-secondary',
                        ];
                    @endphp
                    <span class="badge {{ $statusClasses[$service->status] ?? 'bg-secondary' }} fs-6">
                        {{ ucfirst($service->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">{{ $service->title }}</h4>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase">Category</label>
                                <div>
                                    @if($service->category)
                                        <span class="badge bg-light text-dark fs-6">{{ $service->category->name }}</span>
                                    @else
                                        <span class="text-muted">No category</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase">Price</label>
                                <div class="h5 mb-0">₦{{ number_format($service->price, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase">Delivery Time</label>
                                <div>{{ $service->delivery_days }} day(s)</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small text-uppercase">Revisions Included</label>
                                <div>{{ $service->revisions_included }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small text-uppercase">Description</label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($service->description)) !!}
                        </div>
                    </div>

                    @if($service->portfolio_links && is_array($service->portfolio_links) && count($service->portfolio_links) > 0)
                        <div class="mb-4">
                            <label class="text-muted small text-uppercase">Portfolio Links</label>
                            <ul class="list-unstyled mb-0">
                                @foreach($service->portfolio_links as $link)
                                    <li>
                                        <a href="{{ $link }}" target="_blank" rel="noopener">
                                            <i class="fas fa-external-link-alt me-2"></i>{{ $link }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($service->status === 'rejected' && $service->rejection_reason)
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong> {{ $service->rejection_reason }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Add-ons -->
            @if($service->addons && $service->addons->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Add-ons ({{ $service->addons->count() }})</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Extra Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($service->addons as $addon)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $addon->name }}</div>
                                            @if($addon->description)
                                                <div class="text-muted small">{{ $addon->description }}</div>
                                            @endif
                                        </td>
                                        <td>₦{{ number_format($addon->price, 2) }}</td>
                                        <td>{{ $addon->delivery_days_extra }} day(s)</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Seller Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Seller Information</h5>
                </div>
                <div class="card-body">
                    @if($service->seller)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($service->seller->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $service->seller->name }}</div>
                                <div class="text-muted small">{{ $service->seller->email }}</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Member Since</label>
                            <div>{{ $service->seller->created_at->format('M d, Y') }}</div>
                        </div>
                        <a href="{{ route('admin.user-details', $service->seller) }}" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-user me-2"></i>View User Profile
                        </a>
                    @else
                        <div class="text-muted">Seller information not available</div>
                    @endif
                </div>
            </div>

            <!-- Service Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Service Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span>{{ $service->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Orders</span>
                        <span>{{ $service->orders()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Active Orders</span>
                        <span>{{ $service->activeOrdersCount() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Reviews</span>
                        <span>{{ $service->reviews()->count() }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($service->status === 'pending')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.professional-services.approve', $service) }}" method="POST" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this service?')">
                                <i class="fas fa-check me-2"></i>Approve Service
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times me-2"></i>Reject Service
                        </button>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 text-danger">Danger Zone</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.professional-services.delete', $service) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this service? This action cannot be undone.')">
                            <i class="fas fa-trash me-2"></i>Delete Service
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.professional-services.reject', $service) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Please provide a clear reason for rejection..."></textarea>
                        <div class="form-text">This will be shared with the seller.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Service</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
