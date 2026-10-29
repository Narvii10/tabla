{{-- Muestra un monto en quetzales: <x-money :amount="$product->price" /> => Q 1,234.50 --}}
@props(['amount' => 0])
<span {{ $attributes->merge(['class' => 'text-nowrap']) }}>Q {{ number_format((float) $amount, 2, '.', ',') }}</span>
