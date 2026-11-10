/**
 * WattWise dashboard interactions (no framework).
 * - Mobile sidebar drawer
 * - <dialog> open/close via data attributes, with re-open after validation errors
 * - Edit-appliance dialog prefilled from the row's data attributes
 * - Recommendation severity filter and appliance search
 */

const onReady = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
    } else {
        callback();
    }
};

const setupSidebar = () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');
    const toggles = document.querySelectorAll('[data-sidebar-toggle]');

    if (!sidebar) {
        return;
    }

    const setOpen = (isOpen) => {
        sidebar.classList.toggle('-translate-x-full', !isOpen);
        overlay?.classList.toggle('hidden', !isOpen);
        toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(isOpen)));
    };

    toggles.forEach((toggle) =>
        toggle.addEventListener('click', () => setOpen(sidebar.classList.contains('-translate-x-full'))),
    );
    overlay?.addEventListener('click', () => setOpen(false));
    sidebar.querySelectorAll('a[href^="#"]').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
};

const setupDialogs = () => {
    document.querySelectorAll('[data-dialog-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const dialog = document.getElementById(trigger.dataset.dialogOpen);

            if (!dialog) {
                return;
            }

            if (trigger.dataset.appliance) {
                fillApplianceForm(dialog, JSON.parse(trigger.dataset.appliance));
            }

            dialog.showModal();
        });
    });

    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.querySelectorAll('[data-dialog-close]').forEach((button) =>
            button.addEventListener('click', () => dialog.close()),
        );

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });

    const dialogWithErrors = document.body.dataset.reopenDialog;
    if (dialogWithErrors) {
        const target = document.getElementById(dialogWithErrors);

        if (target instanceof HTMLDialogElement) {
            target.showModal();
        } else {
            target?.scrollIntoView({ block: 'start' });
        }
    }
};

const showPhotoPreview = (form, url) => {
    const preview = form.querySelector('[data-photo-preview]');
    const placeholder = form.querySelector('[data-photo-placeholder]');
    if (!preview) {
        return;
    }
    preview.src = url ?? '';
    preview.classList.toggle('hidden', !url);
    placeholder?.classList.toggle('hidden', Boolean(url));
};

const setupPhotoInputs = () => {
    document.querySelectorAll('[data-photo-input]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            const form = input.closest('form');
            showPhotoPreview(form, file ? URL.createObjectURL(file) : null);
            const remove = form.querySelector('[data-photo-remove]');
            if (file && remove) {
                remove.checked = false;
            }
        });
    });
};

const fillApplianceForm = (dialog, appliance) => {
    const form = dialog.querySelector('form');
    form.action = appliance.update_url;

    const photoInput = form.querySelector('[data-photo-input]');
    if (photoInput) {
        photoInput.value = '';
    }
    const removePhoto = form.querySelector('[data-photo-remove]');
    if (removePhoto) {
        removePhoto.checked = false;
        removePhoto.closest('label').classList.toggle('hidden', !appliance.photo_url);
    }
    showPhotoPreview(form, appliance.photo_url);

    Object.entries(appliance).forEach(([field, value]) => {
        const input = form.elements.namedItem(field);
        if (input && 'value' in input && input.type !== 'file') {
            input.value = value;
        }
    });

    const title = dialog.querySelector('[data-dialog-title]');
    if (title) {
        title.textContent = `Ubah ${appliance.name}`;
    }
};

const setupRecommendationFilter = () => {
    const tabs = document.querySelectorAll('[data-severity-filter]');
    const items = document.querySelectorAll('[data-severity]');

    tabs.forEach((tab) =>
        tab.addEventListener('click', () => {
            const severity = tab.dataset.severityFilter;

            tabs.forEach((other) => other.setAttribute('aria-pressed', String(other === tab)));
            items.forEach((item) => {
                item.hidden = severity !== 'all' && item.dataset.severity !== severity;
            });
        }),
    );
};

const setupApplianceSearch = () => {
    const search = document.querySelector('[data-appliance-search]');
    const rows = document.querySelectorAll('[data-appliance-row]');
    const emptyMessage = document.querySelector('[data-appliance-search-empty]');

    search?.addEventListener('input', () => {
        const query = search.value.trim().toLowerCase();
        const visibleNames = new Set();

        rows.forEach((row) => {
            const matches = row.dataset.applianceRow.includes(query);
            row.hidden = !matches;
            if (matches) {
                visibleNames.add(row.dataset.applianceRow);
            }
        });

        emptyMessage?.toggleAttribute('hidden', visibleNames.size > 0);
    });
};

const rupiah = (value) => `Rp${Math.round(value).toLocaleString('id-ID')}`;
const decimal = (value, digits = 1) =>
    value.toLocaleString('id-ID', { minimumFractionDigits: digits, maximumFractionDigits: digits });
const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/**
 * Landing page calculator. Mirrors EnergyCalculationEngine:
 * kWh/month = watt × hours/day × usage factor ÷ 1000 × 30 days × quantity,
 * and a small subset of the expert rules (ExpertRuleSeeder) for instant tips.
 */
const setupLandingCalculator = () => {
    const root = document.querySelector('[data-calculator]');
    if (!root) {
        return;
    }

    const tariffs = JSON.parse(root.querySelector('[data-calculator-tariffs]').textContent);
    const tariffSelect = root.querySelector('[data-calculator-tariff]');
    const rows = [...root.querySelectorAll('[data-calculator-item]')];
    const output = (name) => root.querySelector(`[data-calculator-${name}]`);
    const shareColors = ['#FFFFFF', '#BFDBFE', '#60A5FA', '#3B82F6', '#1D4ED8'];
    const daysPerMonth = 30;

    const readItems = () =>
        rows
            .filter((row) => row.querySelector('[data-calculator-enabled]').checked)
            .map((row) => {
                const watt = Number(row.dataset.watt);
                const factor = Number(row.dataset.factor);
                const hours = Number(row.querySelector('[data-calculator-hours]').value);
                const quantity = Math.max(1, Number(row.querySelector('[data-calculator-quantity]').value) || 1);
                const kwh = ((watt * hours * factor) / 1000) * daysPerMonth * quantity;

                return { key: row.dataset.key, name: row.dataset.name, category: row.dataset.category, watt, factor, hours, quantity, kwh };
            });

    const buildTips = (items, totalKwh, loadRatio, rate) => {
        const tips = [];
        const add = (severity, text, savedKwh) => tips.push({ severity, text, saving: Math.max(0, savedKwh) * rate });

        if (loadRatio >= 0.85) {
            add(0, 'Jangan nyalakan AC, pompa air, setrika, dan rice cooker bersamaan agar MCB tidak turun.', 0);
        }

        items.forEach((item) => {
            const share = totalKwh > 0 ? (item.kwh / totalKwh) * 100 : 0;

            if (share >= 35 && item.kwh >= 50) {
                add(1, `${item.name} memakai ${decimal(share, 0)}% listrik Anda. Kurangi 1–2 jam per hari.`, item.kwh * (1.5 / item.hours));
            }
            if (item.key === 'ac' && item.hours >= 8) {
                add(2, `Setel ${item.name} di suhu 24–25°C dan bersihkan filternya rutin.`, item.kwh * 0.18);
            }
            if (item.category === 'lighting' && item.watt >= 25) {
                add(3, `Ganti ${item.name.toLowerCase()} (${item.watt} watt) dengan lampu LED 9 watt.`, ((item.watt - 9) * item.hours * daysPerMonth * item.quantity) / 1000);
            }
            if (item.category === 'entertainment' && item.hours >= 20) {
                add(3, `${item.name} menyala hampir seharian. Matikan saat tidur atau bepergian.`, (item.watt * 8 * daysPerMonth * item.quantity) / 1000);
            }
            if (item.category === 'water_pump' && item.kwh >= 40) {
                add(2, `${item.name} cukup boros. Pakai timer dan cek kebocoran pipa atau tandon.`, item.kwh * 0.25);
            }
        });

        return tips.sort((a, b) => a.severity - b.severity || b.saving - a.saving);
    };

    const render = () => {
        const tariff = tariffs.find((t) => String(t.id) === tariffSelect.value) ?? tariffs[0];
        const rate = tariff?.rate_per_kwh ?? 0;
        const items = readItems().sort((a, b) => b.kwh - a.kwh);
        const totalKwh = items.reduce((sum, item) => sum + item.kwh, 0);
        const cost = totalKwh * rate + (items.length ? tariff?.base_fee ?? 0 : 0);
        const simultaneousWatt = items.reduce((sum, item) => sum + item.watt * item.quantity, 0);
        const loadRatio = tariff?.power_limit_va ? simultaneousWatt / tariff.power_limit_va : 0;

        output('cost').textContent = rupiah(cost);
        output('mobile-cost').textContent = rupiah(cost);
        output('kwh').textContent = decimal(totalKwh);
        output('empty').classList.toggle('hidden', items.length > 0);

        const share = output('share');
        const top = output('top');
        share.replaceChildren();
        top.replaceChildren();

        items.slice(0, 5).forEach((item, index) => {
            const percent = totalKwh > 0 ? (item.kwh / totalKwh) * 100 : 0;
            const segment = document.createElement('span');
            segment.style.width = `${Math.max(1, percent)}%`;
            segment.style.background = shareColors[index];
            share.append(segment);

            if (index < 3) {
                const li = document.createElement('li');
                li.className = 'flex items-center gap-2.5';
                li.innerHTML = `<span class="size-2.5 shrink-0 rounded-full" style="background:${shareColors[index]}"></span>
                    <span class="min-w-0 flex-1 truncate text-blue-50"></span>
                    <span class="font-semibold tabular-nums"></span>
                    <span class="w-24 text-right text-blue-200 tabular-nums"></span>`;
                li.children[1].textContent = item.name;
                li.children[2].textContent = `${decimal(percent, 0)}%`;
                li.children[3].textContent = rupiah(item.kwh * rate);
                top.append(li);
            }
        });

        const otherKwh = items.slice(5).reduce((sum, item) => sum + item.kwh, 0);
        if (otherKwh > 0) {
            const segment = document.createElement('span');
            segment.style.width = `${(otherKwh / totalKwh) * 100}%`;
            segment.style.background = '#475569';
            share.append(segment);
        }

        const loadPercent = loadRatio * 100;
        output('load-percent').textContent = `${decimal(loadPercent, 0)}%`;
        const loadBar = output('load-bar');
        loadBar.style.width = `${Math.min(100, loadPercent)}%`;
        loadBar.classList.remove('bg-emerald-400', 'bg-amber-400', 'bg-red-400');
        loadBar.classList.add(loadPercent >= 100 ? 'bg-red-400' : loadPercent >= 85 ? 'bg-amber-400' : 'bg-emerald-400');
        output('load-text').textContent =
            `${simultaneousWatt.toLocaleString('id-ID')} W dari daya ${tariff?.power_limit_va.toLocaleString('id-ID')} VA. ` +
            (loadPercent >= 100
                ? 'MCB bisa turun jika semua alat menyala bersamaan.'
                : loadPercent >= 85
                  ? 'Mendekati batas, atur giliran alat berdaya besar.'
                  : 'Masih aman.');

        const tips = buildTips(items, totalKwh, loadRatio, rate);
        const tipList = output('tips');
        tipList.replaceChildren();
        tips.slice(0, 4).forEach((tip) => {
            const li = document.createElement('li');
            li.className = 'flex gap-3';
            li.innerHTML = `<span class="mt-1.5 size-2 shrink-0 rounded-full ${tip.severity === 0 ? 'bg-red-500' : tip.severity === 1 ? 'bg-amber-500' : 'bg-electric'}"></span>
                <span class="flex-1 text-slate-700"></span>
                <span class="shrink-0 font-semibold text-emerald-700 tabular-nums"></span>`;
            li.children[1].textContent = tip.text;
            li.children[2].textContent = tip.saving > 0 ? `−${rupiah(tip.saving)}` : '';
            tipList.append(li);
        });
        output('no-tips').classList.toggle('hidden', tips.length > 0 || items.length === 0);
        output('saving').textContent = `${rupiah(tips.reduce((sum, tip) => sum + tip.saving, 0))}/bln`;
    };

    rows.forEach((row) => {
        const hours = row.querySelector('[data-calculator-hours]');
        const hoursLabel = row.querySelector('[data-calculator-hours-label]');
        const quantity = row.querySelector('[data-calculator-quantity]');
        const enabled = row.querySelector('[data-calculator-enabled]');

        hours.addEventListener('input', () => {
            hoursLabel.textContent = `${decimal(Number(hours.value), Number(hours.value) % 1 ? 1 : 0)} jam/hari`;
            enabled.checked = true;
            render();
        });
        quantity.addEventListener('input', () => {
            enabled.checked = true;
            render();
        });
        enabled.addEventListener('change', render);
        row.querySelectorAll('[data-calculator-step]').forEach((button) =>
            button.addEventListener('click', () => {
                quantity.value = Math.min(20, Math.max(1, (Number(quantity.value) || 1) + Number(button.dataset.calculatorStep)));
                enabled.checked = true;
                render();
            }),
        );
    });
    tariffSelect.addEventListener('change', render);

    const mobileBar = output('mobile-bar');
    const inputsColumn = rows[0]?.closest('ul');
    const results = root.querySelector('#hasil');
    if ('IntersectionObserver' in window && inputsColumn && results) {
        let inputsVisible = false;
        let resultsVisible = false;
        const update = () => {
            const show = inputsVisible && !resultsVisible;
            mobileBar.classList.toggle('translate-y-full', !show);
            mobileBar.setAttribute('aria-hidden', String(!show));
            mobileBar.querySelector('a').tabIndex = show ? 0 : -1;
        };
        new IntersectionObserver(([entry]) => {
            inputsVisible = entry.isIntersecting;
            update();
        }).observe(inputsColumn);
        new IntersectionObserver(([entry]) => {
            resultsVisible = entry.isIntersecting;
            update();
        }).observe(results);
    }

    render();
};

const setupCountUp = () => {
    document.querySelectorAll('[data-count-to]').forEach((element) => {
        const target = Number(element.dataset.countTo);
        if (prefersReducedMotion() || !target) {
            return;
        }

        const duration = 1200;
        const start = performance.now();
        const tick = (now) => {
            const progress = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - progress, 3);
            element.textContent = Math.round(target * eased).toLocaleString('id-ID');
            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };
        requestAnimationFrame(tick);
    });
};

onReady(() => {
    setupSidebar();
    setupDialogs();
    setupRecommendationFilter();
    setupApplianceSearch();
    setupPhotoInputs();
    setupLandingCalculator();
    setupCountUp();
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('details[data-user-menu][open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });
});

const setupAvatarPreview = () => {
    const input = document.querySelector('[data-avatar-input]');

    if (!input) {
        return;
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        const preview = document.querySelector('[data-avatar-preview]');

        if (!file || !preview) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        document.querySelector('[data-avatar-current]')?.classList.add('hidden');
        document.querySelector('[data-avatar-save]')?.classList.remove('hidden');
        document.querySelector('[data-avatar-label]').textContent = 'Pilih lagi';
    });
};

onReady(setupAvatarPreview);
