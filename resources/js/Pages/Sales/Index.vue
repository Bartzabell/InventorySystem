<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SearchableDropdown from '@/Components/SearchableDropdown.vue';
import Receipt from '@/Pages/Sales/Receipt.vue';

const props = defineProps({
    sales: Object,
    inventory: Array,
    filters: Object
});

const search = ref(props.filters.search || '');

const showReceipt = ref(false);
const selectedSale = ref(null);

const openReceipt = (sale) => {
    selectedSale.value = sale;
    showReceipt.value = true;
};

const printReceipt = () => {
    window.print();
};

// Watch for search input and debounce API call
watch(search, (value) => {
    router.get(route('sales.index'), { search: value }, { preserveState: true, replace: true });
}, { deep: true });

const selectedItem = ref(null);
const form = useForm({
    item_id: '',
    item_qty: 1,
    customer: ''
});

// Computed properties for selected item details
const selectedItemDetails = computed(() => {
    if (!selectedItem.value) return null;
    const item = props.inventory.find(i => i.id === selectedItem.value);
    return item ? {
        ...item,
        total: (item.item_price * form.item_qty).toFixed(2)
    } : null;
});

const submit = () => {
    form.post(route('sales.store'), {
        onSuccess: () => {
            form.reset();
            selectedItem.value = null;
        }
    });
};

const cancelSale = (id) => {
    if (confirm('Are you sure you want to cancel this sale?')) {
        router.delete(route('sales.destroy', id));
    }
};
</script>

<template>
    <AppLayout title="Sales">
        <!-- Create Sale Form -->
        <div class="p-6 bg-white rounded shadow">
            <h2 class="mb-4 text-xl font-bold">Create New Sale</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Item Selection -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="block mb-2 text-sm font-medium">Select Item</label>
                        <SearchableDropdown
                        v-model="selectedItem"
                        :items="inventory"
                        placeholder="Search for an item..."
                        @change="form.item_id = $event.id"
                        />
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium">Customer Name</label>
                        <input
                            type="text"
                            v-model="form.customer"
                            class="w-full p-2 border rounded"
                            placeholder="Enter customer name"
                        >
                    </div>
                </div>

                <!-- Quantity and Price Details -->
                <div v-if="selectedItemDetails" class="p-4 mt-4 border rounded">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block mb-2 text-sm font-medium">Quantity</label>
                            <input
                                type="number"
                                v-model="form.item_qty"
                                :max="selectedItemDetails.item_qty"
                                min="1"
                                class="w-full p-2 border rounded"
                            >
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Unit Price</label>
                            <p class="p-2">₱{{ selectedItemDetails.item_price }}</p>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Total Amount</label>
                            <p class="p-2">₱{{ selectedItemDetails.total }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                        :disabled="!selectedItem || form.processing"
                    >
                        Complete Sale
                    </button>
                </div>
            </form>
        </div>

        <!-- Sales History -->
        <div class="p-6 mt-4 bg-white rounded shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold">Sales History</h2>
                <input
                    type="text"
                    v-model="search"
                    placeholder="Search sales..."
                    class="p-2 border rounded"
                >
            </div>

            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left border">Item</th>
                        <th class="p-2 text-left border">Customer</th>
                        <th class="p-2 text-left border">Quantity</th>
                        <th class="p-2 text-left border">Amount</th>
                        <th class="p-2 text-left border">Date</th>
                        <th class="p-2 text-left border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in sales.data" :key="sale.id">
                        <td class="p-2 border">{{ sale.created_by.item_code }}</td>
                        <td class="p-2 border">{{ sale.customer }}</td>
                        <td class="p-2 border">{{ sale.item_qty }}</td>
                        <td class="p-2 border">₱{{ sale.amount_sold }}</td>
                        <td class="p-2 border">{{ new Date(sale.created_at).toLocaleDateString() }}</td>
                        <td class="p-2 border">
                            <div class="gap-x-2">
                                <button
                                    @click="cancelSale(sale.id)"
                                    class="p-2 text-red-500 hover:text-red-700"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="openReceipt(sale)"
                                    class="text-blue-500 hover:text-blue-700"
                                >
                                    Print
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="showReceipt && selectedSale"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="p-4 bg-white rounded-lg">
                    <div class="flex justify-end mb-4">
                        <button
                            @click="showReceipt = false"
                            class="text-gray-500 hover:text-gray-700"
                        >
                            Close
                        </button>
                    </div>
                    <Receipt
                        :sale="selectedSale"
                        storeName="Inventory System"
                        storeAddress="Dasmarinas Cavite"
                        storePhone="+63 999 999 9999"
                    />
                    <div class="flex justify-center mt-4">
                        <button
                            @click="printReceipt"
                            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                        >
                            Print Receipt
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-end mt-4">
                <button
                    v-if="sales.prev_page_url"
                    @click="router.get(sales.prev_page_url)"
                    class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                >
                    Previous
                </button>
                <button
                    v-if="sales.next_page_url"
                    @click="router.get(sales.next_page_url)"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>
