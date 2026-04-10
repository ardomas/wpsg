
/*
    wp.apiFetch.use(
        wp.apiFetch.createNonceMiddleware(WPSG_CHILDREN.nonce)
    );

    wp.apiFetch({
        path: '/wpsg/v1/children'
    }).then(response => {
        console.log('Context:', WPSG_CHILDREN.context);
        console.log(response);
    });
*/

document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById('wpsg-children-table-body');

    if (!tbody) return;

    wp.apiFetch({
        path: '/wpsg/v1/children?type=child&limit=50&offset=0',
        headers: {
            'X-WP-Nonce': WPSG_CHILDREN.nonce
        }
    })
    .then(response => {

        tbody.innerHTML = '';

        if (!response || response.length === 0) {
            tbody.innerHTML = `
                <tr class="wpsg-row-blank-data">
                    <td colspan="5" style="text-align:center;">
                        Belum ada data anak.
                    </td>
                </tr>`;
            return;
        }

        response.forEach(child => {

            const age = child.birth_date
                ? calcAge(child.birth_date)
                : '-';

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${escapeHtml(child.name ?? '(noname)')}</td>
                    <td>${age}</td>
                    <td>${escapeHtml(child.parent_name ?? '(noname)')}</td>
                    <td>${escapeHtml(child.status ?? 'Active')}</td>
                    <td>
                        <a href="?action=edit&id=${child.id}">Edit</a>
                    </td>
                </tr>
            `);
        });
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `
            <tr class="wpsg-row-blank-data">
                <td colspan="5" style="text-align:center;color:red;">
                    Gagal memuat data
                </td>
            </tr>`;
    });

});

function calcAge(birthDate) {
    const birth = new Date(birthDate);
    const now   = new Date();
    let age     = now.getFullYear() - birth.getFullYear();
    const m     = now.getMonth()    - birth.getMonth();
    if (m < 0 || (m === 0 && now.getDate() < birth.getDate())) {
        age--;
    }
    return age;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
