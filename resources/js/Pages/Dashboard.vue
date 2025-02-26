<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import ApexCharts from 'apexcharts'

const chartOptions = ref({
    chart: {
        height: 350,
        type: 'line',
        zoom: { enabled: false },
        toolbar: { show: true }
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0'],
    title: { text: 'Yearly Sales Overview', align: 'left' },
    grid: {
        borderColor: '#e7e7e7',
        row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 }
    },
    markers: { size: 5 },
    xaxis: { categories: [], title: { text: 'Month' } },
    yaxis: { title: { text: 'Amount Sold ($)' } },
    legend: {
        position: 'top', horizontalAlign: 'right', floating: true,
        offsetY: -25, offsetX: -5
    },
    noData: { text: 'No data available', align: 'center', verticalAlign: 'middle', style: { color: '#000', fontSize: '16px' } },
    responsive: [{ breakpoint: 600, options: { chart: { height: 240 }, legend: { show: false } } }]
});

const series = ref([]);
const loading = ref(true);
const apiUrl = '/api/sales/yearly';

const fetchChartData = async () => {
    loading.value = true;
    const timestamp = new Date().getTime();
    const response = await axios.get(`${apiUrl}?t=${timestamp}`);
    series.value = response.data.series;
    chartOptions.value.xaxis.categories = response.data.labels || [];
    loading.value = false;
};

onMounted(() => {
    fetchChartData();
    setInterval(fetchChartData, 300000); // Auto-refresh every 5 minutes
});
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="p-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <h3 class="mb-2 text-lg font-medium text-gray-700">Sales Performance</h3>
                    <div v-if="loading" class="flex items-center justify-center h-80">
                        <div class="w-12 h-12 border-t-2 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                    </div>
                    <div v-else class="h-96">
                        <apexchart type="line" height="100%" :options="chartOptions" :series="series"></apexchart>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
