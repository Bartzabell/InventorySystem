<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    inventories: Array
});

const form = useForm({
    id: null,
    name: '',
    quantity: '',
    price: ''
});

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

const edit = (inventory) => {
    form.id = inventory.id;
    form.name = inventory.name;
    form.quantity = inventory.quantity;
    form.price = inventory.price;
    editing.value = true;
};

const deleteItem = (id) => {
    if (confirm('Are you sure?')) {
        form.delete(route('inventory.destroy', id));
    }
};

const resetForm = () => {
    form.id = null;
    form.name = '';
    form.quantity = '';
    form.price = '';
};
</script>

<template>
    <div class="p-6 bg-white rounded shadow">
        <h2 class="mb-4 text-xl font-bold">Inventory Management</h2>

        <form @submit.prevent="submit" class="mb-4">
            <div class="grid grid-cols-3 gap-4">
                <input type="text" v-model="form.name" placeholder="Item Name" class="p-2 border rounded">
                <input type="number" v-model="form.quantity" placeholder="Quantity" class="p-2 border rounded">
                <input type="number" v-model="form.price" placeholder="Price" class="p-2 border rounded">
            </div>
            <button type="submit" class="px-4 py-2 mt-2 text-white bg-blue-500 rounded">
                {{ editing ? 'Update' : 'Add' }}
            </button>
        </form>

        <table class="w-full border border-collapse border-gray-200">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Name</th>
                    <th class="p-2 border">Quantity</th>
                    <th class="p-2 border">Price</th>
                    <th class="p-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="inventory in inventories" :key="inventory.id">
                    <td class="p-2 border">{{ inventory.name }}</td>
                    <td class="p-2 border">{{ inventory.quantity }}</td>
                    <td class="p-2 border">{{ inventory.price }}</td>
                    <td class="p-2 border">
                        <button @click="edit(inventory)" class="text-blue-500">Edit</button>
                        <button @click="deleteItem(inventory.id)" class="ml-2 text-red-500">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
