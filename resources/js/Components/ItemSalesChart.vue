<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    height: {
        type: String,
        default: '400px'
    },
    refreshInterval: {
        type: Number,
        default: 300000 // 5 minutes
    }
});

const chartOptions = ref({
    chart: {
        height: 350,
        type: 'bar',
        stacked: false,
        toolbar: { show: true },
        zoom: { enabled: true }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '60%',
            endingShape: 'rounded'
        },
    },
    dataLabels: { enabled: false },
    stroke: { width: 1, colors: ['#fff'] },
    colors: ['#008FFB', '#00E396', '#FEB019', '#FF4560', '#775DD0', '#546E7A', '#26a69a', '#D10CE8'],
    title: { text: 'Items Sold by Month', align: 'left' },
    grid: {
        borderColor: '#e7e7e7',
        row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 }
    },
    xaxis: {
        categories: [],
        title: { text: 'Month' }
    },
    yaxis: {
        title: { text: 'Quantity Sold' },
        min: 0
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        floating: true,
        offsetY: -25,
        offsetX: -5
    },
    noData: {
        text: 'No data available',
        align: 'center',
        verticalAlign: 'middle',
        style: { color: '#000', fontSize: '16px' }
    },
    tooltip: {
        y: {
            formatter: function (val) {
                return val.toFixed(0) + " units"
            }
        }
    },
    responsive: [
        {
            breakpoint: 600,
            options: {
                chart: { height: 240 },
                legend: { show: false }
            }
        }
    ]
});

const series = ref([]);
const loading = ref(true);
const year = ref(new Date().getFullYear());
const apiUrl = '/api/sales/items';

const fetchChartData = async () => {
    loading.value = true;
    try {
        const timestamp = new Date().getTime();
        const response = await axios.get(`${apiUrl}?year=${year.value}&t=${timestamp}`);

        if (response.data.series) {
            series.value = response.data.series;
            chartOptions.value.xaxis.categories = response.data.labels || [];
        }
    } catch (error) {
        console.error('Error fetching item sales data:', error);
    } finally {
        loading.value = false;
    }
};

const changeYear = (newYear) => {
    year.value = newYear;
    fetchChartData();
};

onMounted(() => {
    fetchChartData();

    if (props.refreshInterval > 0) {
        setInterval(fetchChartData, props.refreshInterval);
    }
});
</script>

<template>
    <div class="p-4 bg-white rounded-lg shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-700">Items Sold by Month ({{ year }})</h3>
            <div class="flex space-x-2">
                <button
                    @click="changeYear(year - 1)"
                    class="px-3 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300"
                >
                    Previous Year
                </button>
                <button
                    @click="changeYear(new Date().getFullYear())"
                    class="px-3 py-1 text-sm text-white bg-blue-500 rounded hover:bg-blue-600"
                >
                    Current Year
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center" :style="{ height: props.height }">
            <div class="w-12 h-12 border-t-2 border-b-2 border-blue-500 rounded-full animate-spin"></div>
        </div>
        <div v-else :style="{ height: props.height }">
            <apexchart type="bar" height="100%" :options="chartOptions" :series="series"></apexchart>
        </div>
    </div>
</template>