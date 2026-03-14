<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Booking Successfully Created') }}</title>
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
        .next-steps { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin: 20px 0; }
        .next-steps h3 { color: #856404; margin-top: 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-container">
                <img src="{{ $message->embed(public_path('images/logo_triptrove_putih.png')) }}" alt="TripTrove" class="logo">
            </div>
            <h1>👋 {{ __('Hello') }}, {{ $booking->user ? $booking->user->name : $booking->guest_name }}!</h1>
        </div>
        
        <div class="content">
            <p>{{ __('Thank you for booking with TripTrove!') }}</p>
            
            <p>{{ __('Here are your booking details') }}:</p>
            
            <div class="panel">
                <p><strong>{{ __('Booking Code') }}:</strong> {{ $booking->booking_code }}</p>
                <p><strong>{{ __('Tour Package') }}:</strong> {{ $package->name }}</p>
                <p><strong>{{ __('Departure Date') }}:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->translatedFormat('d F Y') }}</p>
                <p><strong>{{ __('Number of Participants') }}:</strong> {{ $booking->num_participants }} {{ __('people') }}</p>
                <p><strong>{{ __('Total Payment') }}:</strong> Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
            </div>

            <center>
                <a href="https://triptrovetravel.com" class="button">{{ __('View Another Package') }}</a>
            </center>

            <p>{{ __('If you have any questions, feel free to contact our support team.') }}</p>
            
            <p>{{ __('Warm regards') }},<br><strong>{{ __('TripTrove Team') }}</strong></p>
        </div>
        
        <div class="footer">
            <p>{{ __('This email was sent automatically. Please do not reply to this email.') }}</p>
            <p>&copy; {{ date('Y') }} TripTrove. {{ __('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
