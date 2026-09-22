<x-mail::message>
{!! nl2br(e($customMessage)) !!}

@if($booking->status === 'available')

<x-mail::button :url="'https://triptrovetravel.com/bookings/' . $booking->id . '/payment'" color="cyan">
🎫 {{ $buttonText }}
</x-mail::button>

@endif

<br>
{{ config('app.name') }}
</x-mail::message>
