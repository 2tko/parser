<template>
    <div>
        <div class="container" style="padding: 100px;">
            <highcharts v-loading="loadingGrowthPercent" :options="growthPercent"></highcharts>
            <highcharts v-loading="loadingFall" :options="fall"></highcharts>
        </div>

        <highcharts v-if="loadingGrowthCompare || growthCompare.series.length > 0" v-loading="loadingGrowthCompare" :options="growthCompare"></highcharts>

        <div class="container" style="padding: 0px 100px 100px 100px;">
            <el-row style="margin-bottom: 10px;">
                <el-input placeholder="Name" @input="filteringProject" v-model="filterName" style="width: 10%;"></el-input>
                <el-button :disabled="projectIds.length === 0" type="success" v-on:click="compareData">Сравнить</el-button>
                <el-button v-on:click="resetCompare">Сбросить Сравнение</el-button>
            </el-row>

            <el-pagination
                background
                layout="prev, pager, next"
                :total="totalPage"
                :page-size="pageSize"
                :current-page.sync="page"
                @current-change="handlePaginate">
            </el-pagination>

            <el-table
                ref="multipleTable"
                @select="handleSelect"
                @selection-change="handleSelectionChange"
                v-loading="loading"
                :data="tableData"
                border
                style="width: 100%">
                <el-table-column
                    type="selection"
                    width="55">
                </el-table-column>

                <el-table-column
                    prop="rating"
                    width="80"
                    align="center"
                    label="Рейтинг">
                </el-table-column>
                <el-table-column
                    align="center"
                    label="Название проекта">
                    <template slot-scope="scope">
                        <a :href="'https://coinmarketcap.com/ru/currencies/' + scope.row.slug" target="_blank">
                            {{scope.row.name}}
                        </a>
                    </template>
                </el-table-column>
                <el-table-column
                    align="center"
                    label="Рейтинг CMaC"
                    prop="cmc_rank">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count"
                    label="Общее число холдеров">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_week"
                    label="Неделя">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_2_weeks"
                    label="2 Недели">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_month"
                    label="Месяц">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_3_month"
                    label="3 Месяца">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_6_month"
                    label="6 Месяцев">
                </el-table-column>
                <el-table-column
                    align="center"
                    prop="total_count_year"
                    label="12 Месяцев">
                </el-table-column>
            </el-table>
            <el-pagination
                background
                layout="prev, pager, next"
                :total="totalPage"
                :page-size="pageSize"
                :current-page.sync="page"
                @current-change="handlePaginate">
            </el-pagination>

            <highcharts v-loading="loadingGrowth" :options="growth"></highcharts>
        </div>
    </div>
</template>

<script>
    import axios from "axios";

    export default {
        data() {
            return {
                loading: false,
                filterName: '',
                tableData: [],
                page: 1,
                totalPage: 0,
                pageSize: 0,
                loadingGrowth: false,
                loadingGrowthCompare: false,
                loadingFall: false,
                loadingGrowthPercent: false,
                projectIds: [],
                growth : {
                    title: {
                        text: 'Прирост холдеров в абосолютном (лист № 1)'
                    },
                    credits: {
                        enabled: false
                    },
                    xAxis: {
                        categories: [],
                    },
                    series: []
                },
                growthCompare : {
                    title: {
                        text: 'Сравнение по РОСТУ холдеров в абсолютном значении'
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
                        text: 'ТОП 20 по оттоку в абсолют'
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
                        text: 'ТОП 20 по росту в %'
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
            this.getProjects();
            this.getGrowthDashboardData();
            this.getFallDashboardData();
            this.getGrowthPercentDashboardData();
        },
        methods: {
            filteringProject() {
                this.page = 1;
                this.getProjects();
            },
            resetCompare() {
                this.growthCompare.series = [];
                this.projectIds = [];
                this.$refs.multipleTable.clearSelection();
            },
            getProjects() {
                this.loading = true;
                axios.get('/projects', {
                    params: {
                        page: this.page,
                        name: this.filterName,
                    },
                }).then(response => {
                    this.tableData = response.data.projects.data;
                    this.totalPage = response.data.projects.total;
                    this.pageSize = response.data.projects.per_page;
                    this.loading = false;
                    this.checkedSelected();
                });
            },
            handlePaginate(page) {
                this.page = page;
                this.getProjects();
                this.getGrowthDashboardData();
            },
            getGrowthDashboardData() {
                this.loadingGrowth = true;
                axios.get('/growth/count-holders-dashboard', {
                    params: {
                        page: this.page
                    }
                }).then(response => {
                    this.loadingGrowth = false;
                    this.growth.series = response.data.growth;
                    this.growth.title.text = 'Прирост холдеров в абосолютном (лист № ' + this.page + ')';
                    this.growth.xAxis.categories = response.data.categories;
                });
            },
            getFallDashboardData() {
                this.loadingFall = true;
                axios.get('/fall/count-holders-dashboard', {
                    params: {
                        page: this.page
                    }
                }).then(response => {
                    this.loadingFall = false;
                    this.fall.series = response.data.fall;
                    this.fall.xAxis.categories = response.data.categories;
                });
            },
            getGrowthPercentDashboardData() {
                this.loadingGrowthPercent = true;
                axios.get('/growth-percent/count-holders-dashboard', {
                    params: {
                        page: this.page
                    }
                }).then(response => {
                    this.loadingGrowthPercent = false;
                    this.growthPercent.series[0].data = response.data.growthPercent;
                });
            },
            compareData () {
                if (this.projectIds.length === 0) {
                    this.resetCompare();
                    return;
                }

                this.loadingGrowthCompare = true;
                axios.get('/growth/count-holders-dashboard/compare', {
                    params: {
                        projectIds: this.projectIds
                    }
                }).then(response => {
                    this.loadingGrowthCompare = false;
                    this.growthCompare.series = response.data.growth;
                    this.growthCompare.xAxis.categories = response.data.categories;
                });
            },
            handleSelectionChange(selectedData) {
                if (selectedData.length > 0) {
                    selectedData.forEach((element) => {
                        if (! this.projectIds.includes(element.id)) {
                            this.projectIds.push(element.id);
                        }
                    });
                }
            },
            handleSelect(selection, row) {
                if (this.projectIds.indexOf(row.id) >= 0) {
                    this.projectIds.splice(this.projectIds.indexOf(row.id), 1);
                }
            },
            checkedSelected() {
                this.tableData.forEach(row => {
                    this.$nextTick(function () {
                        if (this.projectIds.indexOf(row.id) >= 0) {
                            this.$refs.multipleTable.toggleRowSelection(row, true);
                        }
                    })
                })
            },
        }
    }
</script>
