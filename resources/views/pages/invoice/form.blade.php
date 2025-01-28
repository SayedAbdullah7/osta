@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('invoice.update', [$model->id])
        : route('invoice.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Paid -->
    <x-group-input-text
        label="Amount"
        name="amount"
        :value="0"
        id="amount"
        max="{{ $model->total  - $model->paid }}"
        type="number"
        min="0"
    />

{{--    <!-- Operation -->--}}
{{--    <x-select--}}
{{--        id="operation"--}}
{{--        label="Operation"--}}
{{--        name="operation"--}}
{{--        :options="['+' => '+', '-' => '-']"--}}
{{--        required--}}
{{--    />--}}

    <!-- Display Result of Paid -->
    <div id="paid-result" class="h2">
        Updated Paid: {{ empty($model->paid) ? 0 : $model->paid }}
    </div>
    <div id="total" class="h2">
        Total: {{ $model->total ?? 0 }}
    </div>
</x-form>

<script>
    $(document).ready(function () {
        // Keep the original paid intact
        let originalPaid = ' {{ empty($model->paid) ? 0 : $model->paid }}';
        let currentPaid = originalPaid; // Updated paid for display

        // Function to calculate and update the paid
        function updatePaid() {
            // Parse the amount input value and ensure it's a number
            let amount = parseFloat($('#amount').val());

            // If the value is not a number or empty, set amount to 0
            if (isNaN(amount) || amount < 0) {
                amount = 0;
            }

            // Get the selected operation (+ or -)
            let operation = $('#operation').val();

            // Reset paid if amount is 0 (we want the original paid in this case)
            if (amount === 0) {
                currentPaid = originalPaid;
            } else {
                // Update the current paid based on the operation
                // if (operation === '+') {
                    currentPaid = 1*originalPaid + amount; // Add to original paid
                // } else if (operation === '-') {
                //     currentPaid = originalPaid - amount; // Subtract from original paid
                // }
            }
            console.log('orginalPaid: ' , originalPaid);
            console.log('currentPaid: ' , currentPaid);
            // Dynamically change the paid result text and apply classes
            $('#paid-result')
                .html(`Updated Paid: ${currentPaid}`)
                .removeClass('text-success text-danger') // Remove previous classes
                .addClass(
                    currentPaid == originalPaid
                        ? '' // No class if paid is unchanged
                        : (currentPaid > originalPaid ? 'text-success' : 'text-danger') // Apply class based on paid
                );
        }



    // Initial call to set the current paid value
        updatePaid();

        // Event listener for changes in the amount input or operation select
        $('#amount, #operation').on('change', function () {
            updatePaid(); // Update paid on input or select change
        });
    });
</script>
