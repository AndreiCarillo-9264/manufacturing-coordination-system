// Lightweight vanilla typeahead module
// Usage: new Typeahead(inputEl, {fetchUrl: '/api/users/search?q=' , minChars:2, onSelect: (item)=>{}})
export default class Typeahead {
    constructor(input, opts = {}) {
        this.input = input;
        this.fetchUrl = opts.fetchUrl || input.dataset.fetchUrl;
        this.minChars = opts.minChars || 2;
        this.onSelect = opts.onSelect || function(){};
        this.debounceMs = opts.debounceMs || 200;
        this.list = null;
        this.activeIndex = -1;
        this.items = [];
        this.timer = null;
        this.init();
    }

    init() {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        this.input.parentNode.insertBefore(wrapper, this.input);
        wrapper.appendChild(this.input);

        this.list = document.createElement('ul');
        this.list.className = 'typeahead-list hidden absolute left-0 right-0 mt-1 bg-white border rounded max-h-48 overflow-auto z-50';
        this.list.setAttribute('role', 'listbox');
        this.input.setAttribute('aria-autocomplete', 'list');
        this.input.setAttribute('aria-expanded', 'false');
        this.input.setAttribute('aria-owns', 'typeahead-list-' + this._uid());
        this.list.id = this.input.getAttribute('aria-owns');

        wrapper.appendChild(this.list);

        this.input.addEventListener('input', this.onInput.bind(this));
        this.input.addEventListener('keydown', this.onKeyDown.bind(this));
        document.addEventListener('click', this.onDocClick.bind(this));
    }

    _uid() { return Math.random().toString(36).slice(2, 9); }

    show() {
        this.list.classList.remove('hidden');
        this.input.setAttribute('aria-expanded', 'true');
    }
    hide() {
        this.list.classList.add('hidden');
        this.input.setAttribute('aria-expanded', 'false');
        this.activeIndex = -1;
    }

    onInput(e) {
        const q = (e.target.value || '').trim();
        if (this.timer) clearTimeout(this.timer);
        if (q.length < this.minChars) {
            this.hide();
            return;
        }
        this.timer = setTimeout(() => this.fetch(q), this.debounceMs);
    }

    async fetch(q) {
        try {
            const resp = await fetch(this.fetchUrl + encodeURIComponent(q));
            if (!resp.ok) return; // fail silently
            const data = await resp.json();
            if (!data.success) return;
            this.items = data.users || [];
            this.render();
        } catch (e) {
            console.error('Typeahead fetch error', e);
            this.items = [];
            this.render();
        }
    }

    render() {
        this.list.innerHTML = '';
        if (!this.items.length) {
            const li = document.createElement('li');
            li.className = 'px-3 py-2 text-sm text-gray-500';
            li.textContent = 'No matches';
            this.list.appendChild(li);
            this.show();
            return;
        }
        this.items.forEach((it, idx) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.dataset.index = idx;
            li.dataset.id = it.id;
            li.dataset.name = it.name;
            li.className = 'px-3 py-2 text-sm hover:bg-blue-50 cursor-pointer';
            li.innerHTML = `<div class="font-medium">${escapeHtml(it.name)}</div><div class="text-xs text-gray-500">${escapeHtml(it.department || '')}</div>`;
            li.addEventListener('click', () => this.select(idx));
            this.list.appendChild(li);
        });
        this.show();
    }

    onKeyDown(e) {
        if (this.list.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            this.move(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            this.move(-1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (this.activeIndex > -1) this.select(this.activeIndex);
        } else if (e.key === 'Escape') {
            e.preventDefault();
            this.hide();
        }
    }

    move(delta) {
        const items = Array.from(this.list.querySelectorAll('li[role="option"]'));
        if (!items.length) return;
        this.activeIndex = Math.max(0, Math.min(items.length - 1, (this.activeIndex === -1 ? 0 : this.activeIndex) + delta));
        items.forEach((el, idx) => el.classList.toggle('bg-blue-50', idx === this.activeIndex));
        const active = items[this.activeIndex];
        if (active) active.scrollIntoView({block: 'nearest'});
    }

    select(idx) {
        const item = this.items[idx];
        if (!item) return;
        this.input.value = item.name;
        this.hide();
        this.onSelect(item);
    }
}

function escapeHtml(s) {
    return String(s || '').replace(/[&"'<>]/g, function (m) { return ({'&':'&amp;','"':'&quot;','\'':"&#39;","<":"&lt;",">":"&gt;"})[m]; });
}
