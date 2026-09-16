

(function () {
    if (typeof TomSelect === 'undefined' || !document.getElementById('avocat-select')) return;

    const avocatsCreateUrl = window.pageData?.avocatsCreateUrl;

    new TomSelect('#avocat-select', {
        create: function (input) {
            window.location.href = avocatsCreateUrl + "?nom=" + encodeURIComponent(input);
            return false;
        },

        placeholder: 'ابحث عن محامٍ ...',

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