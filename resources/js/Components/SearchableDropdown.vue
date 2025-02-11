<script setup>
import { ref, watch, onMounted } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
  modelValue: [String, Number],
  items: {
    type: Array,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Search items...'
  }
});

const emit = defineEmits(['update:modelValue', 'change']);

const searchQuery = ref('');
const isOpen = ref(false);
const filteredItems = ref([]);
const selectedLabel = ref('');
const inputRef = ref(null);

const updateFilteredItems = () => {
  filteredItems.value = props.items
    .filter(item =>
      item.item_code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      item.item_description.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
    .slice(0, 100);
};

const debouncedSearch = debounce(updateFilteredItems, 300);

watch(searchQuery, () => {
  debouncedSearch();
});

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const selected = props.items.find(item => item.id === newValue);
    if (selected) {
      selectedLabel.value = `${selected.item_code} - ${selected.item_description} (Stock: ${selected.item_qty})`;
    }
  } else {
    selectedLabel.value = '';
  }
}, { immediate: true });

const selectItem = (item) => {
  emit('update:modelValue', item.id);
  emit('change', item);
  searchQuery.value = '';
  isOpen.value = false;
};

const handleClick = () => {
  isOpen.value = true;
  if (inputRef.value) {
    setTimeout(() => {
      inputRef.value.focus();
    }, 0);
  }
};

const dropdownRef = ref(null);
onMounted(() => {
  document.addEventListener('click', (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
      isOpen.value = false;
    }
  });
});
</script>

<template>
  <div class="relative" ref="dropdownRef">
    <div
      class="relative w-full border rounded cursor-pointer select-none"
      @mousedown.prevent="handleClick"
    >
      <div class="w-full p-2">
        <template v-if="isOpen">
          <input
            ref="inputRef"
            v-model="searchQuery"
            type="text"
            class="w-full outline-none cursor-text"
            :placeholder="placeholder"
          >
        </template>
        <template v-else>
          <span class="block w-full truncate">
            {{ selectedLabel || placeholder }}
          </span>
        </template>
      </div>
    </div>

    <div
      v-if="isOpen"
      class="absolute z-50 w-full mt-1 overflow-auto bg-white border rounded shadow-lg max-h-60"
    >
      <div
        v-if="filteredItems.length === 0"
        class="p-2 text-gray-500"
      >
        No items found
      </div>
      <div
        v-for="item in filteredItems"
        :key="item.id"
        @mousedown.prevent="selectItem(item)"
        class="p-2 cursor-pointer hover:bg-gray-100"
      >
        {{ item.item_code }} - {{ item.item_description }} (Stock: {{ item.item_qty }})
      </div>
      <div
        v-if="filteredItems.length === 100"
        class="p-2 text-sm text-gray-500 bg-gray-50"
      >
        Showing first 100 results. Please refine your search if needed.
      </div>
    </div>
  </div>
</template>