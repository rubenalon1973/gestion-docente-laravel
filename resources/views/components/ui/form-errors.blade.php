@if ($errors->any())
    <x-ui.alert type="error">
        <p class="font-semibold">Revisa los datos indicados:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif
