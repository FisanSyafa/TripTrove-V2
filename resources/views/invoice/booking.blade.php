<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Invoice') }} - {{ $booking->booking_code }}</title>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            padding: 20px;
            background: #f5f5f5;
        }
        .invoice-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #007bff;
        }
        .logo-section { flex: 1; }
        .logo { max-width: 150px; height: auto; }
        .company-info { margin-top: 10px; font-size: 12px; color: #666; }
        .invoice-info { text-align: right; }
        .invoice-title { 
            font-size: 32px; 
            font-weight: bold; 
            color: #007bff;
            margin-bottom: 10px;
        }
        .invoice-meta { font-size: 14px; color: #666; }
        .invoice-meta p { margin: 5px 0; }
        
        .section { margin-bottom: 30px; }
        .section-title { 
            font-size: 16px; 
            font-weight: bold; 
            color: #007bff;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .two-column { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 30px;
        }
        
        .info-box { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .info-box p { margin: 5px 0; font-size: 14px; }
        .info-box strong { color: #007bff; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0;
        }
        th { 
            background: #007bff; 
            color: white; 
            padding: 12px; 
            text-align: left;
            font-weight: 600;
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid #e9ecef;
        }
        tr:last-child td { border-bottom: none; }
        
        .total-section { 
            margin-top: 20px; 
            text-align: right;
        }
        .total-row { 
            display: flex; 
            justify-content: flex-end; 
            margin: 8px 0;
            font-size: 14px;
        }
        .total-row.grand-total { 
            font-size: 18px; 
            font-weight: bold; 
            color: #007bff;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #007bff;
        }
        .total-label { 
            width: 200px; 
            text-align: right; 
            padding-right: 20px;
        }
        .total-value { 
            width: 150px; 
            text-align: right; 
            font-weight: 600;
        }
        
        .status-badge { 
            display: inline-block; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 12px; 
            font-weight: bold;
        }
        .status-confirmed { background: #28a745; color: white; }
        .status-waiting { background: #ffc107; color: #856404; }
        .status-completed { background: #17a2b8; color: white; }
        
        .footer { 
            margin-top: 50px; 
            padding-top: 20px; 
            border-top: 2px solid #e9ecef;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .print-button { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            background: #007bff; 
            color: white; 
            border: none; 
            padding: 12px 24px; 
            border-radius: 5px; 
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .print-button:hover { background: #0056b3; }
        
        @media screen and (max-width: 600px) {
            .invoice-container { padding: 20px; }
            .header { flex-direction: column; text-align: center; }
            .invoice-info { text-align: center; margin-top: 20px; }
            .two-column { grid-template-columns: 1fr; gap: 20px; }
            .total-row { justify-content: space-between; }
            .total-label { width: auto; text-align: left; padding-right: 10px; }
            .total-value { width: auto; }
            table { font-size: 12px; }
            th, td { padding: 8px 5px; }
            .logo { margin: 0 auto; }
        }
        
        @media print {
            body { background: white; padding: 0; }
            .invoice-container { box-shadow: none; padding: 20px; }
            .print-button { display: none; }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ {{ __('Print Invoice') }}</button>
    
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_triptrove_hitam.png') }}" alt="TripTrove" class="logo">
                <div class="company-info">
                    <strong>TripTrove Travel & Tours</strong><br>
                    {{ __('Your Trusted Travel Partner') }}<br>
                    Email: triptrove.trip@gmail.com<br>
                    Phone: +{{ config('app.admin_whatsapp_number', '6285122605855') }}
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">{{ __('INVOICE') }}</div>
                <div class="invoice-meta">
                    <p><strong>{{ __('Invoice No') }}:</strong> {{ $booking->booking_code }}</p>
                    <p><strong>{{ __('Date') }}:</strong> {{ \Carbon\Carbon::parse($booking->created_at)->translatedFormat('d F Y') }}</p>
                    <p><strong>{{ __('Status') }}:</strong> 
                        @if($booking->status === 'confirmed')
                            <span class="status-badge status-confirmed">{{ __('Confirmed') }}</span>
                        @elseif($booking->status === 'waiting_confirmation')
                            <span class="status-badge status-waiting">{{ __('Waiting Confirmation') }}</span>
                        @elseif($booking->status === 'completed')
                            <span class="status-badge status-completed">{{ __('Completed') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Customer & Booking Info -->
        <div class="section">
            <div class="two-column">
                <div>
                    <div class="section-title">{{ __('Bill To') }}</div>
                    <div class="info-box">
                        <p><strong>{{ __('Name') }}:</strong> {{ $booking->user ? $booking->user->name : $booking->guest_name }}</p>
                        <p><strong>{{ __('Email') }}:</strong> {{ $booking->contact_email }}</p>
                        <p><strong>{{ __('Phone') }}:</strong> {{ $booking->contact_phone }}</p>
                    </div>
                </div>
                <div>
                    <div class="section-title">{{ __('Trip Information') }}</div>
                    <div class="info-box">
                        <p><strong>{{ __('Departure Date') }}:</strong> {{ \Carbon\Carbon::parse($booking->departure_date)->translatedFormat('d F Y') }}</p>
                        <p><strong>{{ __('Pickup Time') }}:</strong> {{ $booking->tourPackage->pickup_time ?: '-' }}</p>
                        <p><strong>{{ __('Duration') }}:</strong> {{ $booking->tourPackage->duration_days }} {{ __('Days') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Package Details -->
        <div class="section">
            <div class="section-title">{{ __('Package Details') }}</div>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Description') }}</th>
                        <th style="text-align: center;">{{ __('Quantity') }}</th>
                        <th style="text-align: right;">{{ __('Unit Price') }}</th>
                        <th style="text-align: right;">{{ __('Discount') }}</th>
                        <th style="text-align: right;">{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $booking->tourPackage->name }}</strong><br>
                            <small style="color: #666;">{{ $booking->tourPackage->destination_summary }}</small>
                        </td>
                        <td style="text-align: center;">
                            {{ $booking->num_adults }} {{ __('Adult') }}
                            @if($booking->num_children > 0)
                                <br>{{ $booking->num_children }} {{ __('Children') }}
                            @endif
                        </td>
                        <td style="text-align: right;">Rp {{ number_format($booking->package_price_at_booking, 0, ',', '.') }}</td>
                        <td style="text-align: right;">{{ $booking->discount_at_booking }}%</td>
                        <td style="text-align: right;">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Facilities Included -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <strong>{{ __('Facilities Included') }}:</strong>
                <ul style="margin: 10px 0 0 20px; font-size: 14px;">
                    @if($booking->tourPackage->includes_hotel)
                        <li>✅ {{ __('Hotel Accommodation') }}</li>
                    @endif
                    @if($booking->tourPackage->includes_guide)
                        <li>✅ {{ __('Professional Tour Guide') }}</li>
                    @endif
                    @if($booking->tourPackage->includes_entrance_fee)
                        <li>✅ {{ __('Attraction Entrance Fees') }}</li>
                    @endif
                    @if($booking->tourPackage->includes_driver_vehicle)
                        <li>✅ {{ __('Transportation (Driver & Vehicle)') }}</li>
                    @endif
                </ul>
            </div>
        </div>

        <!-- Assigned Resources (if confirmed) -->
        @if($booking->status === 'confirmed' && ($booking->driver || $booking->guide || $booking->vehicle))
        <div class="section">
            <div class="section-title">{{ __('Assigned Resources') }}</div>
            <div class="info-box">
                @if($booking->driver)
                    <p><strong>🚗 {{ __('Driver') }}:</strong> {{ $booking->driver->name }}</p>
                @endif
                @if($booking->guide)
                    <p><strong>🎤 {{ __('Tour Guide') }}:</strong> {{ $booking->guide->name }}</p>
                @endif
                @if($booking->vehicle)
                    <p><strong>🚐 {{ __('Vehicle') }}:</strong> {{ $booking->vehicle->name }} ({{ $booking->vehicle->license_plate }})</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Payment Info -->
        <div class="section">
            <div class="section-title">{{ __('Payment Information') }}</div>
            <div class="info-box">
                @php
                    $payment = \App\Models\Payment::where('booking_id', $booking->id)->latest()->first();
                @endphp
                
                @if($payment)
                    <p><strong>{{ __('Payment Method') }}:</strong> {{ ucfirst($payment->payment_method) }}</p>
                    @if($payment->transaction_id)
                        <p><strong>{{ __('Transaction ID') }}:</strong> {{ $payment->transaction_id }}</p>
                    @endif
                    <p><strong>{{ __('Payment Date') }}:</strong> {{ \Carbon\Carbon::parse($payment->created_at)->translatedFormat('d F Y H:i') }}</p>
                    <p><strong>{{ __('Payment Status') }}:</strong> <span style="color: #28a745; font-weight: bold;">{{ __('Paid') }}</span></p>
                @else
                    <p><strong>{{ __('Payment Method') }}:</strong> {{ __('Pay on Arrival') }}</p>
                    <p><strong>{{ __('Payment Status') }}:</strong> <span style="color: #ffc107; font-weight: bold;">{{ __('Pending - Pay on Arrival') }}</span></p>
                    <p style="margin-top: 10px; color: #666; font-size: 13px;">
                        {{ __('Payment will be made upon meeting with our team.') }}
                    </p>
                @endif
            </div>
        </div>

        <!-- Total -->
        <div class="total-section">
            @php
                $pricePerPerson = $booking->package_price_at_booking;
                $subtotal = $pricePerPerson * $booking->num_participants;
                $discountAmount = $subtotal * ($booking->discount_at_booking / 100);
            @endphp
            
            <div class="total-row">
                <div class="total-label">{{ __('Subtotal') }}:</div>
                <div class="total-value">Rp {{ number_format($subtotal, 0, ',', '.') }}</div>
            </div>
            
            @if($booking->discount_at_booking > 0)
            <div class="total-row">
                <div class="total-label">{{ __('Discount') }} ({{ $booking->discount_at_booking }}%):</div>
                <div class="total-value" style="color: #dc3545;">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</div>
            </div>
            @endif
            
            <div class="total-row grand-total">
                <div class="total-label">{{ __('TOTAL') }}:</div>
                <div class="total-value">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Special Requests -->
        @if($booking->special_requests)
        <div class="section">
            <div class="section-title">{{ __('Special Requests') }}</div>
            <div class="info-box">
                <p>{{ $booking->special_requests }}</p>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ __('Thank you for choosing TripTrove!') }}</strong></p>
            <p>{{ __('For any inquiries, please contact us at triptrove.trip@gmail.com or') }} +{{ config('app.admin_whatsapp_number', '6285122605855') }}</p>
            <p style="margin-top: 10px; font-size: 11px;">{{ __('This is a computer-generated invoice and does not require a signature.') }}</p>
        </div>
    </div>

    <script>
        // Auto-focus untuk print dialog (opsional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
