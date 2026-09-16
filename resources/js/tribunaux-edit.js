
document.addEventListener('DOMContentLoaded', function () {

    let regionSelect = document.getElementById('region');
    let provinceSelect = document.getElementById('province');

    let selectedProvinceId = window.pageData?.selectedProvinceId ?? '';

    function loadProvinces(regionId, selectedProvince = null) {

        provinceSelect.innerHTML = '<option value="">جاري التحميل...</option>';

        if (!regionId) {
            provinceSelect.innerHTML = '<option value="">— اختر الإقليم —</option>';
            return;
        }

        fetch(`/api/regions/${regionId}/provinces`, {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(res => {
            if (!res.ok) throw new Error("Error loading provinces");
            return res.json();
        })
        .then(data => {

            provinceSelect.innerHTML = '<option value="">— اختر الإقليم —</option>';

            data.forEach(province => {

                let selected = (province.id == selectedProvince) ? 'selected' : '';

                provinceSelect.innerHTML += `
                    <option value="${province.id}" ${selected}>
                        ${province.province}
                    </option>
                `;
            });
        })
        .catch(err => {
            console.error(err);
            provinceSelect.innerHTML = '<option value="">تعذر تحميل الأقاليم</option>';
        });
    }

    regionSelect.addEventListener('change', function () {
        loadProvinces(this.value);
    });

    if (regionSelect.value) {
        loadProvinces(regionSelect.value, selectedProvinceId);
    }

});