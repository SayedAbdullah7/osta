<x-app-layout>

    <x-dynamic-table
        table-id="contactor-request-table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
    />

</x-app-layout>
