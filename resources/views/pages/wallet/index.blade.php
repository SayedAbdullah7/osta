<x-app-layout>

    <x-dynamic-table
        table-id="wallets_table"
        :columns="$columns"
        :filters="$filters"
        :actions="false"
    />

</x-app-layout>
