function token(name, fallback) {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    if (!raw) {
        return fallback;
    }

    return raw.startsWith('#') ? raw : `rgb(${raw})`;
}

function signal(id, status) {
    window.dispatchEvent(new CustomEvent('fs-chart', { detail: { id, status } }));
}

function money(amount) {
    const symbol = document.querySelector('[data-admin-charts]')?.dataset.currency || '$';

    return `${symbol}${Number(amount).toFixed(2)}`;
}

function legend(items) {
    const list = document.createElement('ul');
    list.className = 'mt-fs-sm flex flex-wrap gap-x-fs-sm gap-y-fs-2xs';

    items.forEach((item) => {
        const entry = document.createElement('li');
        entry.className = 'inline-flex items-center gap-fs-2xs text-sm text-text';
        const swatch = document.createElement('span');
        swatch.className = 'size-3 shrink-0 rounded-fs-xs';
        swatch.style.background = item.color;
        swatch.setAttribute('aria-hidden', 'true');
        const label = document.createElement('span');
        label.textContent = item.label;
        entry.append(swatch, label);
        list.append(entry);
    });

    return list;
}

function svgNode(tag, attrs) {
    const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
    Object.entries(attrs).forEach(([key, value]) => node.setAttribute(key, String(value)));

    return node;
}

function lineChart(canvas, labels, series, colors) {
    const width = 640;
    const height = 160;
    const max = Math.max(1, ...series.flatMap((row) => row.values));
    const svg = svgNode('svg', {
        viewBox: `0 0 ${width} ${height}`,
        class: 'h-40 w-full',
        role: 'img',
        'aria-hidden': 'true',
    });
    const plotLeft = 8;
    const plotRight = 8;
    const plotTop = 8;
    const plotBottom = 8;
    const innerWidth = width - plotLeft - plotRight;
    const innerHeight = height - plotTop - plotBottom;

    series.forEach((row, seriesIndex) => {
        const points = row.values.map((value, index) => {
            const x = plotLeft + (row.values.length === 1 ? innerWidth / 2 : (index / (row.values.length - 1)) * innerWidth);
            const y = plotTop + innerHeight - (value / max) * innerHeight;

            return `${x},${y}`;
        }).join(' ');
        svg.append(svgNode('polyline', {
            points,
            fill: 'none',
            stroke: colors[seriesIndex],
            'stroke-width': '2.5',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
        }));
    });

    const scale = document.createElement('p');
    scale.className = 'text-sm text-text';
    scale.textContent = `Highest point on this chart: ${max}`;

    const weeks = document.createElement('div');
    weeks.className = 'mt-fs-2xs grid gap-fs-3xs text-sm text-text';
    weeks.style.gridTemplateColumns = `repeat(${labels.length}, minmax(0, 1fr))`;
    labels.forEach((label) => {
        const cell = document.createElement('span');
        cell.className = 'truncate text-center';
        cell.textContent = label;
        weeks.append(cell);
    });

    canvas.append(
        scale,
        svg,
        weeks,
        legend(series.map((row, index) => ({ label: row.label, color: colors[index] }))),
    );
}

function drawRegistrations(canvas, data) {
    const colors = [
        token('--fs-tide', '#0E7C74'),
        token('--fs-tide-deep', '#0A5C56'),
        token('--fs-warning', '#9A5416'),
        token('--fs-text', '#102027'),
    ];
    lineChart(canvas, data.labels, data.series, colors);
}

function drawFunnel(canvas, data) {
    const list = document.createElement('div');
    list.className = 'grid gap-fs-sm';
    const tide = token('--fs-tide', '#0E7C74');

    data.stages.forEach((stage) => {
        const row = document.createElement('div');
        const head = document.createElement('div');
        head.className = 'flex items-baseline justify-between gap-fs-sm text-sm text-text';
        const name = document.createElement('span');
        name.className = 'font-medium';
        name.textContent = stage.label;
        const count = document.createElement('span');
        count.textContent = String(stage.count);
        head.append(name, count);
        const track = document.createElement('div');
        track.className = 'mt-fs-3xs h-2 rounded-fs-xl bg-surface-sunken';
        const bar = document.createElement('div');
        bar.className = 'h-2 rounded-fs-xl';
        bar.style.background = tide;
        bar.style.width = `${stage.width}%`;
        track.append(bar);
        row.append(head, track);
        list.append(row);
    });

    canvas.append(list);
}

function drawOrders(canvas, data) {
    const orderColor = token('--fs-tide', '#0E7C74');
    const revenueColor = token('--fs-warning', '#9A5416');
    const maxOrders = Math.max(1, ...data.orders);
    const maxRevenue = Math.max(1, ...data.revenue);
    const svg = svgNode('svg', {
        viewBox: '0 0 640 160',
        class: 'h-40 w-full',
        role: 'img',
        'aria-hidden': 'true',
    });
    const slot = 640 / data.labels.length;

    data.orders.forEach((count, index) => {
        const barHeight = (count / maxOrders) * 140;
        svg.append(svgNode('rect', {
            x: index * slot + slot * 0.28,
            y: 150 - barHeight,
            width: slot * 0.44,
            height: Math.max(barHeight, count > 0 ? 4 : 0),
            rx: '4',
            fill: orderColor,
        }));
        const revenueY = 150 - (data.revenue[index] / maxRevenue) * 140;
        if (index > 0) {
            const previousY = 150 - (data.revenue[index - 1] / maxRevenue) * 140;
            svg.append(svgNode('line', {
                x1: (index - 1) * slot + slot / 2,
                y1: previousY,
                x2: index * slot + slot / 2,
                y2: revenueY,
                stroke: revenueColor,
                'stroke-width': '2.5',
                'stroke-linecap': 'round',
            }));
        }
    });

    const note = document.createElement('p');
    note.className = 'text-sm text-text';
    note.textContent = `Bars use the order count. The line uses revenue, up to ${money(Math.max(...data.revenue))}.`;

    const weeks = document.createElement('div');
    weeks.className = 'mt-fs-2xs grid gap-fs-3xs text-sm text-text';
    weeks.style.gridTemplateColumns = `repeat(${data.labels.length}, minmax(0, 1fr))`;
    data.labels.forEach((label) => {
        const cell = document.createElement('span');
        cell.className = 'truncate text-center';
        cell.textContent = label;
        weeks.append(cell);
    });

    canvas.append(
        note,
        svg,
        weeks,
        legend([
            { label: 'Placed orders', color: orderColor },
            { label: 'Revenue', color: revenueColor },
        ]),
    );
}

function drawProducts(canvas, data) {
    const max = Math.max(1, ...data.rows.map((row) => row.units));
    const list = document.createElement('div');
    list.className = 'grid gap-fs-sm';
    const tide = token('--fs-tide', '#0E7C74');

    data.rows.forEach((row) => {
        const wrap = document.createElement('div');
        const head = document.createElement('div');
        head.className = 'flex items-baseline justify-between gap-fs-sm text-sm text-text';
        const name = document.createElement('span');
        name.className = 'min-w-0 truncate font-medium';
        name.textContent = row.name;
        const meta = document.createElement('span');
        meta.className = 'shrink-0';
        meta.textContent = `${row.units} sold · ${money(row.revenue)}`;
        head.append(name, meta);
        const track = document.createElement('div');
        track.className = 'mt-fs-3xs h-2 rounded-fs-xl bg-surface-sunken';
        const bar = document.createElement('div');
        bar.className = 'h-2 rounded-fs-xl';
        bar.style.background = tide;
        bar.style.width = `${Math.max(8, Math.round((row.units / max) * 100))}%`;
        track.append(bar);
        wrap.append(head, track);
        list.append(wrap);
    });

    canvas.append(list);
}

const drawers = {
    registrations: drawRegistrations,
    funnel: drawFunnel,
    orders: drawOrders,
    products: drawProducts,
};

export function startAdminCharts(host) {
    const payload = JSON.parse(document.getElementById('admin-charts')?.textContent || 'null');

    if (!payload) {
        throw new Error('Missing admin chart data.');
    }

    host.querySelectorAll('[data-chart]').forEach((panel) => {
        const id = panel.getAttribute('data-chart');
        const draw = drawers[id];
        const canvas = panel.querySelector('[data-chart-canvas]');

        if (!draw || !canvas || !payload[id]) {
            signal(id, 'error');
            return;
        }

        try {
            draw(canvas, payload[id]);
            signal(id, 'ready');
        } catch {
            signal(id, 'error');
        }
    });
}
