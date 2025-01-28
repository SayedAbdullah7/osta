{{--<x-app-layout>--}}
{{--    <x-slot name="toolbar">--}}
{{--        --}}{{--        @include('partials.toolbar')--}}
{{--    </x-slot>--}}
{{--    <x-table action="{{route('user.create')}}">--}}
{{--        {{ $dataTable->table() }}--}}
{{--    </x-table>--}}
{{--    @push('scripts')--}}
{{--        {{ $dataTable->scripts() }}--}}
{{--    @endpush--}}
{{--</x-app-layout>--}}
<x-app-layout>

    <x-dynamic-table
        table-id="users_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('user.create')"

    />

</x-app-layout>
