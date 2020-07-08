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

function getAttrs_updateRow(el, type) {
    let data = {};

    data['id'] = el.getAttribute('row-id')
    data['field'] = el.getAttribute('row-field')
    data['model'] = el.getAttribute('row-model')
    data['value'] = undefined;

    switch (type) {
        case 'checkbox':
            data['value'] = +$(el).is(':checked');
            break;
        case 'select_dropdown':
            data['value'] = +$(el).val();
        default:
            break;
    }

    if (typeof data.value == 'undefined') console.warn("getAttrs_updateRow() cannot catch the value")

    return data;
}

function checkbox_updateRow(el) {
    let attrs = getAttrs_updateRow(el, 'checkbox')

    let data = {};
    data[attrs.field] = attrs.value;

    $.ajax({
        method: "PUT",
        url: `/api/${attrs.model}/${attrs.id}`,
        data
    })
}

function select_dropdown_updateRow(el) {
    let attrs = getAttrs_updateRow(el, 'select_dropdown')

    let data = {};
    data[attrs.field] = attrs.value;

    $.ajax({
        method: "PUT",
        url: `/api/${attrs.model}/${attrs.id}`,
        data
    })
}