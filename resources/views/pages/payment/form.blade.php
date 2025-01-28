@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('wallet.update', [$model->id])
        : route('wallet.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Balance -->
    <x-group-input-text
        label="Amount"
        name="amount"
        :value="0"
        id="amount"
    />

    <!-- Operation -->
    <x-select
        id="operation"
        label="Operation"
        name="operation"
        :options="['+' => '+', '-' => '-']"
        required
    />

    <!-- Display Result of Balance -->
    <div id="balance-result" class="h2">
        Updated Balance: {{ $model->balance }}
    </div>
</x-form>

<script>
    $(document).ready(function () {
        // Keep the original balance intact
        let originalBalance = {{ $isEdit ? $model->balance : 0 }};
        let currentBalance = originalBalance; // Updated balance for display

        // Function to calculate and update the balance
        function updateBalance() {
            // Parse the amount input value and ensure it's a number
            let amount = parseFloat($('#amount').val());

            // If the value is not a number or empty, set amount to 0
            if (isNaN(amount) || amount < 0) {
                amount = 0;
            }

            // Get the selected operation (+ or -)
            let operation = $('#operation').val();

            // Reset balance if amount is 0 (we want the original balance in this case)
            if (amount === 0) {
                currentBalance = originalBalance;
            } else {
                // Update the current balance based on the operation
                if (operation === '+') {
                    currentBalance = originalBalance + amount; // Add to original balance
                } else if (operation === '-') {
                    currentBalance = originalBalance - amount; // Subtract from original balance
                }
            }
            console.log('orginalBalance: ' , originalBalance);
            console.log('currentBalance: ' , currentBalance);
            // Dynamically change the balance result text and apply classes
            $('#balance-result')
                .html(`Updated Balance: ${currentBalance}`)
                .removeClass('text-success text-danger') // Remove previous classes
                .addClass(
                    currentBalance == originalBalance
                        ? '' // No class if balance is unchanged
                        : (currentBalance > originalBalance ? 'text-success' : 'text-danger') // Apply class based on balance
                );
        }

        // Initial call to set the current balance value
        updateBalance();

        // Event listener for changes in the amount input or operation select
        $('#amount, #operation').on('change', function () {
            updateBalance(); // Update balance on input or select change
        });
    });
</script>
