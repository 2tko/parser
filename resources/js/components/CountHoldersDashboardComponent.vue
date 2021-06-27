<template>
    <div class="container" style="padding: 100px;">
        <highcharts v-loading="loading" :options="growth"></highcharts>
        <highcharts v-loading="loading" :options="fall"></highcharts>
        <highcharts v-loading="loading" :options="growthPercent"></highcharts>
    </div>
</template>

<script>
import axios from "axios";

export default {
    name: "CountHoldersDashboardComponent",
    data() {
        return {
            loading: false,
            growth : {
                title: {
                    text: 'TOП 20 по РОСТУ холдеров в абсолютном значении'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: [],
                },
                series: []
            },
            fall : {
                title: {
                    text: 'TOП 20 по ОТТОКУ холдеров в абсолютном значении'
                },
                credits: {
                    enabled: false
                },
                xAxis: {
                    categories: [],
                },
                series: []
            },
            growthPercent: {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'ТОП 20 по РОСТУ холдеров в %'
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y:.1f}%'
                        }
                    }
                },
                credits: {
                    enabled: false
                },
                series: [
                    {
                        name: "рост в %",
                        colorByPoint: true,
                        data: []
                    }
                ],
                xAxis: {
                    type: 'category'
                },
            }
        }
    },
    mounted() {
        this.getGrowthDashboardData();
        this.getFallDashboardData();
        this.getGrowthPercentDashboardData();
    },
    methods: {
        getGrowthDashboardData() {
            this.loading = true;
            axios.get('/growth/count-holders-dashboard', {}).then(response => {
                this.loading = false;
                this.growth.series = response.data.growth;
                this.growth.xAxis.categories = response.data.categories;
            });
        },
        getFallDashboardData() {
            this.loading = true;
            axios.get('/fall/count-holders-dashboard', {}).then(response => {
                this.loading = false;
                this.fall.series = response.data.fall;
                this.fall.xAxis.categories = response.data.categories;
            });
        },
        getGrowthPercentDashboardData() {
            this.loading = true;
            axios.get('/growth-percent/count-holders-dashboard', {}).then(response => {
                this.loading = false;
                this.growthPercent.series[0].data = response.data.growthPercent;
            });
        },
    }
}
</script>

<style scoped>

</style>
