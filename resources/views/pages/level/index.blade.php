<x-app-layout>

    <x-dynamic-table
        table-id="levels_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
        :create-url="route('level.create')"
    />

</x-app-layout>
