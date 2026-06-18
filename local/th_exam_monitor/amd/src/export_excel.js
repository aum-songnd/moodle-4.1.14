define([], function() {

    const escapeHtml = function(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    /**
     * Lấy text thuần từ dữ liệu DataTables.
     *
     * @param {*} value
     * @returns {String}
     */
    const extractText = function(value) {

        if (value && typeof value === 'object') {
            if (value.export !== undefined) {
                return extractText(value.export);
            }
            if (value.sort !== undefined) {
                return extractText(value.sort);
            }
            if (value.display !== undefined) {
                return extractText(value.display);
            }
        }

        if (value && value.jquery) {
            return value.text();
        }

        if (typeof HTMLElement !== 'undefined' && value instanceof HTMLElement) {
            return value.textContent || '';
        }

        if (typeof value === 'string') {
            const div = document.createElement('div');
            div.innerHTML = value;
            return div.textContent || '';
        }

        return String(value ?? '');
    };

    /**
     * Lấy HTML export của 1 ô, ưu tiên giữ lại thẻ img.
     *
     * @param {*} value
     * @returns {String}
     */
    const extractExportHtml = function(value) {
        let html = value;

        if (value && typeof value === 'object') {
            if (value.export !== undefined) {
                html = value.export;
            } else if (value.display !== undefined) {
                html = value.display;
            } else if (value.sort !== undefined) {
                html = value.sort;
            }
        }

        if (html && html.jquery) {
            const wrapper = document.createElement('div');
            wrapper.appendChild(html[0].cloneNode(true));
            html = wrapper.innerHTML;
        } else if (typeof HTMLElement !== 'undefined' && html instanceof HTMLElement) {
            const wrapper = document.createElement('div');
            wrapper.appendChild(html.cloneNode(true));
            html = wrapper.innerHTML;
        }

        if (typeof html !== 'string') {
            return `<span>${escapeHtml(extractText(html))}</span>`;
        }

        const div = document.createElement('div');
        div.innerHTML = html;

        const imgs = div.querySelectorAll('img');
        if (imgs.length > 0) {
            imgs.forEach(function(img) {
                const src = img.getAttribute('src') || '';
                const alt = img.getAttribute('alt') || '';
                const width = img.getAttribute('width') || img.style.width || '80';
                const height = img.getAttribute('height') || img.style.height || '80';

                if (!src) {
                    img.remove();
                    return;
                }

                img.removeAttribute('srcset');
                img.removeAttribute('loading');
                img.removeAttribute('decoding');
                img.removeAttribute('style');

                img.setAttribute('src', src);
                img.setAttribute('alt', alt);
                img.setAttribute('width', width);
                img.setAttribute('height', height);
            });

            return div.innerHTML;
        }

        return `<span>${escapeHtml(div.textContent || '')}</span>`;
    };

    /**
     * Gửi HTML sang PHP để tạo file Excel.
     *
     * @param {String} html
     * @param {Number|String} courseid
     */
    const submitHtml = function(html, courseid) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = M.cfg.wwwroot + '/local/th_exam_monitor/export_excel.php';
        form.target = '_blank';

        const htmlInput = document.createElement('input');
        htmlInput.type = 'hidden';
        htmlInput.name = 'html';
        htmlInput.value = html;
        form.appendChild(htmlInput);

        if (courseid !== undefined && courseid !== null) {
            const courseInput = document.createElement('input');
            courseInput.type = 'hidden';
            courseInput.name = 'courseid';
            courseInput.value = courseid;
            form.appendChild(courseInput);
        }

        const sesskeyInput = document.createElement('input');
        sesskeyInput.type = 'hidden';
        sesskeyInput.name = 'sesskey';
        sesskeyInput.value = M.cfg.sesskey;
        form.appendChild(sesskeyInput);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    /**
     * Export DataTable ra Excel, giữ được ảnh trong HTML cell.
     *
     * @param {Object} dt
     * @param {Number|String} courseid
     * @returns {void}
     */
    const exportExcel = function(dt, courseid) {

        const headers = dt.columns().header().toArray().map(function(th) {
            return escapeHtml(th.textContent.trim());
        });

        headers.push('Ghi chú');

        const rows = dt.rows({
            search: 'applied',
            order: 'applied'
        }).indexes().toArray();

        let html = '<table border="1" cellpadding="4" cellspacing="0">';
        html += '<thead><tr>';

        headers.forEach(function(h) {
            html += `<th>${h}</th>`;
        });

        html += '</tr></thead><tbody>';

        rows.forEach(function(rowIndex) {
            html += '<tr>';

            headers.forEach(function(_, colIndex) {
                if (colIndex === headers.length - 1) {
                    html += '<td></td>';
                    return;
                }

                const raw = dt.cell(rowIndex, colIndex).render('export');
                const cellHtml = extractExportHtml(raw);
                html += `<td>${cellHtml}</td>`;
            });

            html += '</tr>';
        });

        html += '</tbody></table>';

        submitHtml(html, courseid);
    };

    return {
        exportExcel: exportExcel
    };
});