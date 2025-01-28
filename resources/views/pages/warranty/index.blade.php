<x-app-layout>
    <x-dynamic-table
        table-id="warranties_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('warranty.create')"
    />

</x-app-layout>
