@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>System Settings</h2>
            <p style="color:#666; font-size:14px">Configure package identification and system behavior</p>
        </div>
        <div>
            <form method="POST" action="{{ route('admin.settings.reset') }}" style="display:inline" onsubmit="return confirm('Are you sure you want to reset all settings to default values?')">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm">Reset to Defaults</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> Please check the following issues:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        
        <!-- Package Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Package Configuration</h5>
                <small class="text-muted">Configure which packages grant intermediate access</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="intermediate_package_id" class="form-label">
                                Intermediate Package ID
                                <small class="text-muted d-block">Primary package ID for intermediate access</small>
                            </label>
                            <select name="intermediate_package_id" id="intermediate_package_id" class="form-select">
                                <option value="">Select Package</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}" 
                                        {{ ($settings['intermediate_package_id']->value ?? '2') == $package->id ? 'selected' : '' }}>
                                        {{ $package->name }} (ID: {{ $package->id }}, Slug: {{ $package->slug }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="intermediate_package_slugs" class="form-label">
                                Intermediate Package Slugs
                                <small class="text-muted d-block">Comma-separated list of package slugs that grant intermediate access</small>
                            </label>
                            <input 
                                type="text" 
                                name="intermediate_package_slugs" 
                                id="intermediate_package_slugs" 
                                class="form-control" 
                                value="{{ $settings['intermediate_package_slugs']->value ?? 'intermediate,upgrade-intermediate' }}"
                                placeholder="intermediate,upgrade-intermediate"
                            >
                        </div>
                    </div>
                </div>
                <div class="alert alert-info">
                    <strong>Note:</strong> Users will have intermediate access if their package matches either the Package ID above OR has one of the slugs listed above.
                </div>
            </div>
        </div>

        <!-- Coaching Configuration -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Coaching Settings</h5>
                <small class="text-muted">Configure coaching booking behavior</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="max_booking_days" class="form-label">Max Booking Days Ahead</label>
                            <input 
                                type="number" 
                                name="coaching.max_booking_days_ahead" 
                                id="max_booking_days" 
                                class="form-control" 
                                value="{{ $settings['coaching.max_booking_days_ahead']->value ?? '30' }}"
                                min="1" max="365"
                            >
                            <small class="text-muted">How many days in advance users can book sessions</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="session_duration" class="form-label">Session Duration (minutes)</label>
                            <input 
                                type="number" 
                                name="coaching.session_duration_minutes" 
                                id="session_duration" 
                                class="form-control" 
                                value="{{ $settings['coaching.session_duration_minutes']->value ?? '60' }}"
                                min="15" max="240"
                            >
                            <small class="text-muted">Default duration for coaching sessions</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="buffer_before" class="form-label">Buffer Before Session (minutes)</label>
                            <input 
                                type="number" 
                                name="coaching.buffer_minutes_before" 
                                id="buffer_before" 
                                class="form-control" 
                                value="{{ $settings['coaching.buffer_minutes_before']->value ?? '10' }}"
                                min="0" max="60"
                            >
                            <small class="text-muted">How early users can join sessions</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="buffer_after" class="form-label">Buffer After Session (minutes)</label>
                            <input 
                                type="number" 
                                name="coaching.buffer_minutes_after" 
                                id="buffer_after" 
                                class="form-control" 
                                value="{{ $settings['coaching.buffer_minutes_after']->value ?? '60' }}"
                                min="0" max="120"
                            >
                            <small class="text-muted">How long after session start users can still join</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Notification Settings</h5>
                <small class="text-muted">Configure email notifications</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="notifications.admin_booking_enabled" 
                                id="admin_booking_notifications"
                                value="1"
                                {{ ($settings['notifications.admin_booking_enabled']->value ?? 'true') === 'true' ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="admin_booking_notifications">
                                Admin Booking Notifications
                                <small class="text-muted d-block">Send email to admins when users create bookings</small>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                name="notifications.user_booking_status_enabled" 
                                id="user_status_notifications"
                                value="1"
                                {{ ($settings['notifications.user_booking_status_enabled']->value ?? 'true') === 'true' ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="user_status_notifications">
                                User Status Notifications
                                <small class="text-muted d-block">Send email to users when booking status changes</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>

    <!-- Current Package Reference -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Available Packages Reference</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->id }}</td>
                            <td>{{ $package->name }}</td>
                            <td><code>{{ $package->slug }}</code></td>
                            <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection