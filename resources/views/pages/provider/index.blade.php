{{--<x-app-layout>--}}
{{--    <x-slot name="toolbar">--}}
{{--        --}}{{--        @include('partials.toolbar')--}}
{{--    </x-slot>--}}
{{--    <x-table action="{{route('provider.create')}}">--}}
{{--        {{ $dataTable->table() }}--}}
{{--    </x-table>--}}
{{--    @push('scripts')--}}
{{--        {{ $dataTable->scripts() }}--}}
{{--    @endpush--}}
{{--</x-app-layout>--}}
<x-app-layout>

    <x-dynamic-table
        table-id="providers_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('provider.create')"

    />

</x-app-layout>
