<x-app-layout>

    <x-dynamic-table
        table-id="conversations_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
{{--        :show-checkbox="true"--}}
{{--        :default-order="['column' => 2, 'direction' => 'desc']"--}}
    />

</x-app-layout>
