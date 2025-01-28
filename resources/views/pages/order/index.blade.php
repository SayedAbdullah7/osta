<x-app-layout>

    <x-dynamic-table
        table-id="orders_id"
        :columns="$columns"
        :filters="$filters"
        :actions="true"
{{--        :show-checkbox="true"--}}
{{--        :default-order="['column' => 2, 'direction' => 'desc']"--}}
    />

</x-app-layout>
