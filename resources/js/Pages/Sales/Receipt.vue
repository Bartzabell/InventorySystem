<!-- Receipt.vue -->
<script setup>
const props = defineProps({
    sale: {
        type: Object,
        required: true
    },
    storeName: {
        type: String,
        default: 'My Store'
    },
    storeAddress: {
        type: String,
        default: '123 Main Street'
    },
    storePhone: {
        type: String,
        default: '(123) 456-7890'
    }
});

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <div class="p-4 receipt-container" style="width: 300px; font-family: 'Courier New', Courier, monospace;">
        <!-- Store Info -->
        <div class="mb-4 text-center">
            <h2 class="text-lg font-bold">{{ storeName }}</h2>
            <p class="text-sm">{{ storeAddress }}</p>
            <p class="text-sm">{{ storePhone }}</p>
            <div class="my-2 border-b border-dashed"></div>
        </div>

        <!-- Receipt Details -->
        <div class="mb-4">
            <p class="text-sm">Receipt #: {{ sale.id }}</p>
            <p class="text-sm">Date: {{ new Date(sale.created_at).toLocaleString() }}</p>
            <p class="text-sm">Customer: {{ sale.customer }}</p>
            <div class="my-2 border-b border-dashed"></div>
        </div>

        <!-- Item Details -->
        <div class="mb-4">
            <div class="flex justify-between mb-2 text-sm">
                <span>Item</span>
                <span>Amount</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">{{ sale.created_by.item_code }}</span>
                <span class="text-sm">₱{{ sale.amount_sold }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-sm">Qty: {{ sale.item_qty }}</span>
                <span class="text-sm">@₱{{ (sale.amount_sold / sale.item_qty).toFixed(2) }}</span>
            </div>
            <div class="my-2 border-b border-dashed"></div>
        </div>

        <!-- Total -->
        <div class="mb-4">
            <div class="flex justify-between font-bold">
                <span>TOTAL</span>
                <span>₱{{ sale.amount_sold }}</span>
            </div>
            <div class="my-2 border-b border-dashed"></div>
        </div>

        <!-- Footer -->
        <div class="text-sm text-center">
            <p>Thank you for your purchase!</p>
            <p>Please come again</p>
        </div>
    </div>
</template>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .receipt-container,
    .receipt-container * {
        visibility: visible;
    }
    .receipt-container {
        position: absolute;
        left: 0;
        top: 0;
    }
}
</style>
