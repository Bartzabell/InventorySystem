<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    inventories: Object, // Pagination returns an object, not an array
    filters: Object
});

const search = ref(props.filters.search || '');

// Watch for search input and debounce API call
watch(search, (value) => {
    router.get(route('inventory.index'), { search: value }, { preserveState: true, replace: true });
}, { deep: true });

const form = useForm({
    id: null,
    item_code: '',
    item_description: '',
    item_qty: '',
    item_price: ''
});

const edit = (inventory) => {
    form.id = inventory.id;
    form.item_code = inventory.item_code;
    form.item_description = inventory.item_description;
    form.item_qty = inventory.item_qty;
    form.item_price = inventory.item_price;
    editing.value = true;
};

const deleteItem = (id) => {
    if (confirm('Are you sure?')) {
        form.delete(route('inventory.destroy', id));
    }
};

const resetForm = () => {
    form.id = null;
    form.item_code = '';
    form.item_description = '';
    form.item_qty = '';
    form.item_price = '';
};

const editing = ref(false);

const submit = () => {
    if (editing.value) {
        form.put(route('inventory.update', form.id), {
            onSuccess: () => {
                resetForm();
                editing.value = false;
            }
        });
    } else {
        form.post(route('inventory.store'), {
            onSuccess: () => resetForm()
        });
    }
};
</script>

<template>
    <AppLayout title="Inventory">
        <div class="p-6 bg-white rounded shadow">
            <h2 class="mb-4 text-xl font-bold">Inventory Management</h2>

            <form @submit.prevent="submit" class="mb-4">
                <div class="flex w-full gap-x-4">
                    <div class="flex flex-col flex-1 gap-y-2">
                        <input type="text" v-model="form.item_code" placeholder="Item Code" class="p-2 border rounded">
                        <input type="text" v-model="form.item_description" placeholder="Description" class="p-2 border rounded">
                    </div>
                    <div class="flex flex-col flex-1 gap-y-2">
                        <input type="number" v-model="form.item_qty" placeholder="Quantity" class="p-2 border rounded">
                        <input type="number" v-model="form.item_price" placeholder="Price" class="p-2 border rounded">
                    </div>
                </div>
                <div class="flex items-center justify-center mt-2">
                    <button type="submit" class="w-auto h-auto px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                        {{ editing ? 'Update' : 'Add Item' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="p-6 mt-2 bg-white rounded shadow">
            <!-- Search Bar -->
            <div class="flex justify-end mb-4">
                <input type="text" v-model="search" placeholder="Search items..." class="p-2 border rounded ">
            </div>
            <!-- Inventory Table -->
            <table class="w-full border border-collapse border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">Quantity</th>
                        <th class="p-2 border">Price</th>
                        <th class="p-2 border">Total Amount</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="inventory in inventories.data" :key="inventory.id">
                        <td class="p-2 border">{{ inventory.item_code }}</td>
                        <td class="p-2 border">{{ inventory.item_qty }}</td>
                        <td class="p-2 border">{{ inventory.item_price }}</td>
                        <td class="p-2 border">{{ inventory.total_amount }}</td>
                        <td class="p-2 border">
                            <button @click="edit(inventory)" class="text-blue-500">Edit</button>
                            <button @click="deleteItem(inventory.id)" class="ml-2 text-red-500">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-end mt-4 mr-4">
                <button
                    v-if="inventories.prev_page_url"
                    @click="router.get(inventories.prev_page_url, {}, { preserveState: true, replace: true })"
                    class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                    Previous
                </button>

                <button
                    v-if="inventories.next_page_url"
                    @click="router.get(inventories.next_page_url, {}, { preserveState: true, replace: true })"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>
