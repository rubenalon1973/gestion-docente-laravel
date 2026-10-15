@props(['href'])

<a href="{{ $href }}" role="menuitem" {{ $attributes }}>{{ $slot }}</a>
