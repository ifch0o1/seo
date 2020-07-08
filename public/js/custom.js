// Example POST method implementation:
async function postData(url = "", data = {}) {
    // Default options are marked with *
    const response = await fetch(url, {
        method: "POST", // *GET, POST, PUT, DELETE, etc.
        mode: "cors", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {
            "Content-Type": "application/json",
            // 'Content-Type': 'application/x-www-form-urlencoded',
        },
        redirect: "follow", // manual, *follow, error
        referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        body: JSON.stringify(data), // body data type must match "Content-Type" header
    });
    return response.json(); // parses JSON response into native JavaScript objects
}

function removeDuplicateCharacters(string) {
    return string
        .split("")
        .filter(function (item, pos, self) {
            return self.indexOf(item) == pos;
        })
        .join("");
}

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
            $(this.$el).empty().select2({ data: options });
        },
    },
    destroyed: function () {
        $(this.$el).off().select2("destroy");
    },
});

function checkbox_updateRow(el) {
    let id = el.getAttribute('row-id')
    let field = el.getAttribute('row-field')
    let model = el.getAttribute('row-model')
    let value = +$(el).is(':checked');

    let data = {};
    data[field] = value;

    $.ajax({
        method: "PUT",
        url: `/api/${model}/${id}`,
        data
    })
}