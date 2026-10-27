@if (!empty($billingBanner))
    <div class="alert alert-{{ $billingBanner['type'] }} alert-dismissible fade show mb-3" role="alert">
        <i class="ri-information-line me-1"></i> {{ $billingBanner['message'] }}
        <a href="{{ route('billing.subscription.index') }}" class="alert-link ms-1">Kelola langganan</a>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
