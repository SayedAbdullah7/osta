<x-app-layout>

    <x-dynamic-table
        table-id="users_table"
        :columns2="[
            ['name' => 'id', 'label' => 'Id'],
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'phone', 'label' => 'Phone'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'is_phone_verified', 'label' => 'Phone Verified'],
            ['name' => 'gender', 'label' => 'Gender'],
            ['name' => 'date_of_birth', 'label' => 'Date of Birth'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'created_at', 'label' => 'Created Date']
    ]"
        :columns="$frontendColumns"
        :filters="$filters"
        :actions="true"
        :show-checkbox="true"
        :default-order="['column' => 2, 'direction' => 'desc']"
        ajax-url="{{ route('datatable.data') }}"
    />

</x-app-layout>
