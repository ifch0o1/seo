{{-- 
    This usually is included in HEAD

    @section('head')
        @include('libs.vue')
    @stop
--}}


{{-- VUE SCRIPT --}}
<script src="{{ asset('js/libs/vue.js') }}"></script>

{{-- SELECT2 TEMPLATE --}}
<script type="text/x-template" id="select2-template">
    <select>
        <slot></slot>
    </select>
</script>

<script>
    Vue.config.devtools = true;

    Vue.component("select2-ajax", {
        props: ["options", "value"],
        template: "#select2-template",
        mounted: function () {
            var vm = this;
            $(this.$el)
                // init select2
                .select2({ data: this.options, width: '100%' })
                .val(this.value)
                .trigger("change")
                // emit event on change.
                .on("change", function () {
                    vm.$emit("input", this.value);
                });
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value).trigger("change");
            },
            options: function (options) {
                // update options
                $(this.$el).empty().select2({ data: options }).val(this.value).trigger('change.select2');
            },
        },
        destroyed: function () {
            $(this.$el).off().select2("destroy");
        },
    });

    Vue.component("select2", {
        props: ["options", "value"],
        template: "#select2-template",
        mounted: function () {
            var vm = this;
            $(this.$el)
                // init select2
                .select2({ data: this.options, width: '100%' })
                .val(this.value)
                .trigger("change")
                // emit event on change.
                .on("change", function () {
                    vm.$emit("input", this.value);
                });
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value).trigger("change");
            },
            options: function (options) {
                // update options
                $(this.$el).empty().select2({ data: options }).val(this.value).trigger('change.select2');
            },
        },
        destroyed: function () {
            $(this.$el).off().select2("destroy");
        },
    });
</script>