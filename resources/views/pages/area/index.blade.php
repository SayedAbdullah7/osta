<x-app-layout>
    <x-slot name="toolbar">
        {{--        @include('partials.toolbar')--}}
    </x-slot>
    <x-table action="{{route('area.create')}}">
        {{ $dataTable->table() }}
    </x-table>
    @push('scripts')
        {{ $dataTable->scripts() }}
    @endpush
</x-app-layout>
