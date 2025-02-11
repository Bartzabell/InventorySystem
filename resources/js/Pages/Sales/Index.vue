<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    sales: Object,
    inventory: Array,
    filters: Object
});

const search = ref(props.filters.search || '');

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
                        <select
                            v-model="selectedItem"
                            @change="form.item_id = selectedItem"
                            class="w-full p-2 border rounded"
                        >
                            <option value="">Select an item</option>
                            <option
                                v-for="item in inventory"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.item_code }} - {{ item.item_description }}
                                (Stock: {{ item.item_qty }})
                            </option>
                        </select>
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
                            <button
                                @click="cancelSale(sale.id)"
                                class="text-red-500 hover:text-red-700"
                            >
                                Cancel
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

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