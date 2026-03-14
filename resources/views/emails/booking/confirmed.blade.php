<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Booking Confirmed') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .email-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #00d4ff, #007bff); color: white; padding: 40px 30px; text-align: center; }
        .logo-container { margin-bottom: 20px; }
        .logo { max-width: 150px; height: auto; }
        .header h1 { margin: 0; font-size: 28px; font-weight: bold; }
        .content { padding: 30px; }
        .panel { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .panel p { margin: 8px 0; }
        .panel strong { color: #007bff; }
        .button { display: inline-block; background: linear-gradient(135deg, #00d4ff, #007bff); color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #888; font-size: 12px; background: #f8f9fa; }
        .detail-section { margin: 20px 0; }
        .detail-section h3 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 15px; }
        ul { padding-left: 20px; }
        li { margin: 8px 0; }
        .emoji { font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/logo_triptrove_putih.png')) }}" alt="TripTrove" class="logo">
            </div>
            <h1>✅ {{ __('Booking Confirmed!') }}</h1>
        </div>
        
        <div class="content">
            <p>{{ __('Hello') }}, <strong>{{ $user->name }}</strong>!</p>
            
            <p>{{ __('Great news! Your booking has been confirmed by our team. Get ready for an unforgettable adventure!') }}</p>
            
            <div class="panel">
                <p><strong>{{ __('Booking Code') }}:</strong> {{ $booking->booking_code }}</p>
                <p><strong>{{ __('Tour Package') }}:</strong> {{ $package->name }}</p>
                <p><strong>{{ __('Destination') }}:</strong> {{ $package->destination_summary }}</p>
                <p><strong>{{ __('Departure Date') }}:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->translatedFormat('l, d F Y') }}</p>
                <p><strong>{{ __('Duration') }}:</strong> {{ $package->duration_days }} {{ __('Days') }}</p>
                <p><strong>{{ __('Number of Participants') }}:</strong> {{ $booking->num_participants }} {{ __('people') }}</p>
            </div>

            @if($booking->driver || $booking->guide || $booking->vehicle)
            <div class="detail-section">
                <h3>{{ __('Your Trip Details') }}</h3>
                @if($booking->driver)
                <p><span class="emoji">🚗</span> <strong>{{ __('Driver') }}:</strong> {{ $booking->driver->name }}</p>
                @endif
                @if($booking->guide)
                <p><span class="emoji">🎤</span> <strong>{{ __('Tour Guide') }}:</strong> {{ $booking->guide->name }}</p>
                @endif
                @if($booking->vehicle)
                <p><span class="emoji">🚐</span> <strong>{{ __('Vehicle') }}:</strong> {{ $booking->vehicle->name }} ({{ $booking->vehicle->license_plate }})</p>
                @endif
            </div>
            @endif

            <div class="detail-section">
                <h3>{{ __('Things to Prepare') }}</h3>
                <ul>
                    <li>{{ __('Make sure to bring your ID (KTP/Passport)') }}</li>
                    <li>{{ __('Prepare comfortable clothes for the trip') }}</li>
                    <li>{{ __('Bring personal medication if needed') }}</li>
                    <li>{{ __('Don\'t forget your camera to capture the moments!') }}</li>
                </ul>
            </div>

            <center>
                <a href="{{ config('app.url') }}/dashboard" class="button">{{ __('View Booking Details') }}</a>
            </center>

            <p>{{ __('If you have any questions or changes, please contact our team immediately.') }}</p>
            
            <p>{{ __('Have a great trip!') }} 🌴</p>
            
            <p>{{ __('Warm regards') }},<br><strong>{{ __('TripTrove Team') }}</strong></p>
        </div>
        
        <div class="footer">
            <p>{{ __('This email was sent automatically. Please do not reply to this email.') }}</p>
            <p>&copy; {{ date('Y') }} TripTrove. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
