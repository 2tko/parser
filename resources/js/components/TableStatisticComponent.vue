<template>
    <div class="container" style="padding: 100px;">
        <el-row style="margin-bottom: 10px;">
            <el-input placeholder="Name" v-model="filterName" style="width: 10%;"></el-input>
            <el-button type="primary" v-on:click="filteringProject">Filter</el-button>
            <el-button v-on:click="resetFilter">Reset</el-button>
        </el-row>

        <el-table
            v-loading="loading"
            :data="tableData"
            border
            style="width: 100%">
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
                label="Общее число холдеров (данные за предыдущие сутки)">
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
            @current-change="handlePaginate">
        </el-pagination>
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
            }
        },
        mounted() {
            this.getProjects();
        },
        methods: {
            filteringProject() {
                if (this.filterName === '') {
                    return;
                }

                this.page = 1;
                this.getProjects();
            },
            resetFilter() {
                this.filterName = '';
                this.getProjects();
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
                });
            },
            handlePaginate(page) {
                this.page = page;
                this.getProjects();
            },
        }
    }
</script>
