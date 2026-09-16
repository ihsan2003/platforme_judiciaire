

(function () {
    if (typeof TomSelect === 'undefined' || !document.getElementById('parties-select')) return;

    const partiesCreateUrl = window.pageData?.partiesCreateUrl;

    new TomSelect('#parties-select', {
        plugins: ['remove_button'],

        create: function (input) {
            window.location.href = partiesCreateUrl + "?nom_partie=" + encodeURIComponent(input);
            return false;
        },

        placeholder: 'ابحث عن طرف ...',

        loadingText: 'جاري البحث...',

        render: {
            no_results: function (data, escape) {
                return `<div class="no-results">لا توجد نتائج</div>`;
            },

            option_create: function (data, escape) {
                return `<div class="create">➕ إضافة "${escape(data.input)}"</div>`;
            }
        }
    });
})();