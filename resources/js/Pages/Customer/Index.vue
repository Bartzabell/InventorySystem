<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    customers: Object, // Pagination returns an object, not an array
    filters: Object
});

const search = ref(props.filters.search || '');

// Watch for search input and debounce API call
watch(search, (value) => {
    router.get(route('customer.index'), { search: value }, { preserveState: true, replace: true });
}, { deep: true });

const form = useForm({
    id: null,
    customer_name: '',
    contact_person: '',
    phone_number: '',
    email: '',
    tin_no: '',
    address: '',
});

const edit = (customer) => {
    form.id = customer.id;
    form.customer_name = customer.customer_name;
    form.contact_person = customer.contact_person;
    form.phone_number = customer.phone_number;
    form.email = customer.email;
    form.tin_no = customer.tin_no;
    form.address = customer.address;
    editing.value = true;
};

const deleteItem = (id) => {
    if (confirm('Are you sure?')) {
        form.delete(route('customer.destroy', id));
    }
};

const resetForm = () => {
    form.id = null,
    form. customer_name = '',
    form.contact_person = '';
    form.phone_number = '';
    form.email = '';
    form.tin_no = '';
    form.address = '';
};

const editing = ref(false);

const submit = () => {
    if (editing.value) {
        form.put(route('customer.update', form.id), {
            onSuccess: () => {
                resetForm();
                editing.value = false;
            }
        });
    } else {
        form.post(route('customer.store'), {
            onSuccess: () => resetForm()
        });
    }
};


</script>

<template>
    <AppLayout title="Customer">
        <div class="p-6 bg-white rounded shadow">
            <h2 class="mb-4 text-xl font-bold">Customer Management</h2>

            <form @submit.prevent="submit" class="mb-4">
                <div class="flex w-full gap-x-4">
                    <div class="flex flex-col flex-1 gap-y-2">
                        <input type="text" v-model="form.customer_name" placeholder="Customer Name" class="p-2 border rounded">
                        <input type="text" v-model="form.contact_person" placeholder="Contact Person" class="p-2 border rounded">
                        <input type="text" v-model="form.phone_number" placeholder="Phone Number" class="p-2 border rounded">
                    </div>
                    <div class="flex flex-col flex-1 gap-y-2">
                        <input type="email" v-model="form.email" placeholder="Email" class="p-2 border rounded">
                        <input type="text" v-model="form.tin_no" placeholder="TIN" class="p-2 border rounded">
                        <input type="text" v-model="form.address" placeholder="Address" class="p-2 border rounded">
                    </div>
                </div>
                <div class="flex items-center justify-center mt-2">
                    <button type="submit" class="w-auto h-auto px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                        {{ editing ? 'Update' : 'Add Customer' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="p-6 mt-2 bg-white rounded shadow">
            <!-- Search Bar -->
            <div class="flex justify-end mb-4">
                <input type="text" v-model="search" placeholder="Search items..." class="p-2 border rounded ">
            </div>
            <!-- Customer Table -->
            <table class="w-full border border-collapse border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Name</th>
                        <th class="p-2 border">CONTACT PERSON</th>
                        <th class="p-2 border">PHONE NO.</th>
                        <th class="p-2 border">ADDRESS</th>
                        <th class="p-2 border">EMAIL</th>
                        <th class="p-2 border">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="customer in customers.data" :key="customer.id">
                        <td class="p-2 border">{{ customer.customer_name }}</td>
                        <td class="p-2 border">{{ customer.contact_person }}</td>
                        <td class="p-2 border">{{ customer.phone_number }}</td>
                        <td class="p-2 border">{{ customer.address }}</td>
                        <td class="p-2 border">{{ customer.email }}</td>
                        <td class="p-2 border">
                            <button @click="edit(customer)" class="text-blue-500">Edit</button>
                            <button @click="deleteItem(customer.id)" class="ml-2 text-red-500">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="flex justify-end mt-4 mr-4">
                <button
                    v-if="customers.prev_page_url"
                    @click="router.get(customers.prev_page_url, {}, { preserveState: true, replace: true })"
                    class="px-4 py-2 mr-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                    Previous
                </button>

                <button
                    v-if="customers.next_page_url"
                    @click="router.get(customers.next_page_url, {}, { preserveState: true, replace: true })"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-700">
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>
