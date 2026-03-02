<template>
    <date-picker :first-day-of-week="7"
                 :value-type="valueType"
                 format="DD-MM-YYYY"
                 lang="pt-br"
                 v-model="value2"
                 v-on:input="changeValue"
                 :input-attr="inputAttr"
                 :input-name="inputName"
    ></date-picker>
</template>

<script>
    import moment from 'moment';

    export default {
        name: "date-picker2",
        props: {
            value: {
                default: ''
            },
            inputName: {
                default: 'date'
            },
            inputAttr: {
                type: Object,
                default() {
                    return {}
                }
            },
        },
        data: () => {
            return {
                value2: null,
                valueType: {
                    value2date: (value) => {
                        if (!value) {
                        } else if (value.match(/\d{2}-\d{2}-\d{4}/)) {
                            return moment(value, 'DD-MM-YYYY').toDate();
                        } else if (value.match(/\d{4}-\d{2}-\d{2}/)) {
                            return moment(value).toDate();
                        }
                        return null;
                    },  // transform the binding value to calendar Date Object
                    date2value: (date) => {
                        if (date) {
                            return moment(date).format('DD-MM-YYYY');
                        }
                        return null
                    }   // transform the calendar Date Object to binding value
                }
            }
        },
        methods: {
            changeValue(value) {
                let val = this.valueType.value2date(value);
                if(val) {
                    val = moment(val).format('YYYY-MM-DD');
                }
                this.$emit('input', val);
            }
        },
        mounted() {
            this.value2 = this.value;
            this.changeValue(this.value2);
            console.log(this.inputAttr2);
        }
    }
</script>

<style scoped>

</style>
