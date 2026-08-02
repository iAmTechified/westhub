import './bootstrap';
import Alpine from 'alpinejs';
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';

window.Alpine = Alpine;
const THEME_KEY = 'westhub-admin-theme';

window.westhubTheme = () => ({
    theme: document.documentElement.dataset.theme || 'dark',
    boot() {
        const preferred = window.localStorage.getItem(THEME_KEY);
        if (preferred === 'light' || preferred === 'dark') {
            this.theme = preferred;
        } else if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            this.theme = 'light';
        }

        this.apply();
    },
    toggle() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        this.apply();
        window.localStorage.setItem(THEME_KEY, this.theme);
    },
    apply() {
        document.documentElement.dataset.theme = this.theme;
    },
});

window.westhubLoginForm = (url) => ({
    loading: false,
    serverFeedback: '',
    requestState: 'idle',
    form: {
        email: '',
        password: '',
        remember: false,
    },
    touched: {
        email: false,
        password: false,
    },
    errors: {
        email: '',
        password: '',
    },
    get requestStateClass() {
        if (this.requestState === 'error') return 'is-error';
        if (this.requestState === 'success') return 'is-success';
        if (this.requestState === 'loading') return 'is-loading';
        return '';
    },
    validateEmail() {
        this.touched.email = true;
        const email = this.form.email.trim();
        if (!email) {
            this.errors.email = 'Email is required.';
            return false;
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            this.errors.email = 'Please provide a valid email address.';
            return false;
        }

        this.errors.email = '';
        return true;
    },
    validatePassword() {
        this.touched.password = true;
        if (!this.form.password) {
            this.errors.password = 'Password is required.';
            return false;
        }

        if (this.form.password.length < 8) {
            this.errors.password = 'Password must be at least 8 characters.';
            return false;
        }

        this.errors.password = '';
        return true;
    },
    validateAll() {
        const emailValid = this.validateEmail();
        const passwordValid = this.validatePassword();
        return emailValid && passwordValid;
    },
    async submit(event) {
        this.serverFeedback = '';
        if (!this.validateAll()) {
            this.requestState = 'idle';
            return;
        }

        this.loading = true;
        this.requestState = 'loading';

        const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.content || '';

        try {
            const response = await window.axios.post(
                url,
                {
                    email: this.form.email.trim(),
                    password: this.form.password,
                    remember: this.form.remember ? 1 : 0,
                    _token: csrfToken,
                },
                {
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                },
            );

            this.requestState = 'success';
            this.serverFeedback = response.data?.message || 'Signed in successfully.';
            const redirect = response.data?.redirect;
            if (redirect) {
                window.location.assign(redirect);
                return;
            }

            // Fallback for non-JSON login responses (302 followed by HTML).
            const responseUrl = response?.request?.responseURL;
            if (responseUrl && responseUrl !== window.location.href) {
                window.location.assign(responseUrl);
                return;
            }

            this.requestState = 'error';
            this.serverFeedback = 'Login succeeded but redirect target was not returned. Please refresh and try again.';
        } catch (error) {
            if (error.response?.status === 419) {
                this.serverFeedback = 'Your session expired. Refreshing page...';
                window.setTimeout(() => {
                    window.location.reload();
                }, 350);
                return;
            }

            if (error.response?.status === 403) {
                this.requestState = 'error';
                this.serverFeedback = 'Your account is authenticated but is not allowed to access this admin panel.';
                return;
            }

            this.requestState = 'error';
            const data = error.response?.data || {};
            const message = data.message || 'Login request failed. Please try again.';
            const fieldErrors = data.errors || {};

            if (Array.isArray(fieldErrors.email) && fieldErrors.email[0]) {
                this.errors.email = fieldErrors.email[0];
            }

            if (Array.isArray(fieldErrors.password) && fieldErrors.password[0]) {
                this.errors.password = fieldErrors.password[0];
            }

            this.serverFeedback = message;
        } finally {
            this.loading = false;
            if (this.requestState === 'loading') {
                this.requestState = 'idle';
            }
        }
    },
});

window.westhubStudioAutosave = (wire) => ({
    idleTimer: null,
    requestCounter: 0,
    pendingRequests: 0,
    nextToken() {
        this.requestCounter += 1;
        return `${Date.now()}-${this.requestCounter}`;
    },
    get isSubmitting() {
        return this.pendingRequests > 0;
    },
    get bodyText() {
        const raw = wire.body || '';
        return String(raw).replace(/<[^>]*>/g, ' ').trim();
    },
    get isFormValid() {
        const title = (wire.title || '').trim();
        const categoryId = wire.article_category_id;
        return title !== '' && this.bodyText !== '' && categoryId !== null && categoryId !== '';
    },
    async callAutosave() {
        const token = this.nextToken();
        this.pendingRequests += 1;
        try {
            await wire.autosaveFromInteraction(token);
        } finally {
            this.pendingRequests = Math.max(0, this.pendingRequests - 1);
        }
    },
    async triggerSaveDraft() {
        if (!this.isFormValid || this.isSubmitting) return;
        const token = this.nextToken();
        this.pendingRequests += 1;
        try {
            await wire.saveDraft(token);
        } finally {
            this.pendingRequests = Math.max(0, this.pendingRequests - 1);
        }
    },
    async triggerPublish() {
        if (!this.isFormValid || this.isSubmitting) return;
        const token = this.nextToken();
        this.pendingRequests += 1;
        try {
            await wire.publish(token);
        } finally {
            this.pendingRequests = Math.max(0, this.pendingRequests - 1);
        }
    },
    onInput() {
        if (this.idleTimer) {
            window.clearTimeout(this.idleTimer);
        }

        this.idleTimer = window.setTimeout(() => {
            this.callAutosave();
        }, 1200);
    },
    onBlur() {
        if (this.idleTimer) {
            window.clearTimeout(this.idleTimer);
        }
        this.callAutosave();
    },
});

window.westhubEditor = (entangledContent) => ({
    editor: null,
    content: entangledContent,
    init() {
        if (this.editor) return;

        this.editor = new Editor({
            element: this.$refs.editor,
            editable: true,
            extensions: [
                StarterKit,
                Underline,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                Link.configure({
                    openOnClick: false,
                    autolink: true,
                }),
                Placeholder.configure({
                    placeholder: 'Write article body content here...',
                }),
            ],
            content: this.content || '',
            editorProps: {
                attributes: {
                    class: 'tiptap-editor-area',
                },
            },
            onUpdate: ({ editor }) => {
                const html = editor.getHTML();
                if (this.content !== html) {
                    this.content = html;
                }
            },
        });

        this.$watch('content', (value) => {
            if (this.editor && value !== this.editor.getHTML()) {
                this.editor.commands.setContent(value || '', false);
            }
        });
    },
    destroy() {
        this.editor?.destroy();
    },
    toggleBold() {
        this.editor?.chain().focus().toggleBold().run();
    },
    toggleItalic() {
        this.editor?.chain().focus().toggleItalic().run();
    },
    toggleUnderline() {
        this.editor?.chain().focus().toggleUnderline().run();
    },
    toggleBulletList() {
        this.editor?.chain().focus().toggleBulletList().run();
    },
    toggleOrderedList() {
        this.editor?.chain().focus().toggleOrderedList().run();
    },
    toggleHeading(level = 2) {
        this.editor?.chain().focus().toggleHeading({ level }).run();
    },
    setParagraph() {
        this.editor?.chain().focus().setParagraph().run();
    },
    setBlockType(type = 'paragraph') {
        if (!this.editor) return;
        if (type === 'paragraph') {
            this.setParagraph();
            return;
        }

        if (type === 'h1') {
            this.toggleHeading(1);
            return;
        }

        if (type === 'h2') {
            this.toggleHeading(2);
            return;
        }

        if (type === 'h3') {
            this.toggleHeading(3);
            return;
        }
    },
    currentBlockType() {
        if (!this.editor) return 'paragraph';
        if (this.editor.isActive('heading', { level: 1 })) return 'h1';
        if (this.editor.isActive('heading', { level: 2 })) return 'h2';
        if (this.editor.isActive('heading', { level: 3 })) return 'h3';
        return 'paragraph';
    },
    toggleBlockquote() {
        this.editor?.chain().focus().toggleBlockquote().run();
    },
    toggleCodeBlock() {
        this.editor?.chain().focus().toggleCodeBlock().run();
    },
    setTextAlign(align = 'left') {
        this.editor?.chain().focus().setTextAlign(align).run();
    },
    insertHorizontalRule() {
        this.editor?.chain().focus().setHorizontalRule().run();
    },
    undo() {
        this.editor?.chain().focus().undo().run();
    },
    redo() {
        this.editor?.chain().focus().redo().run();
    },
    clearFormatting() {
        this.editor?.chain().focus().unsetAllMarks().clearNodes().run();
    },
    unsetLink() {
        this.editor?.chain().focus().unsetLink().run();
    },
    isActive(type, attrs = {}) {
        if (!this.editor) return false;
        if (typeof type === 'object' && type !== null) {
            return this.editor.isActive(type);
        }
        return this.editor.isActive(type, attrs);
    },
    setLink() {
        const url = window.prompt('Enter URL');
        if (!url) return;
        this.editor?.chain().focus().setLink({ href: url }).run();
    },
});

window.westhubAdminSelect = (config = {}) => ({
    open: false,
    options: [],
    value: '',
    inputObserver: null,
    sourceObserver: null,
    syncValueFromInputBound: null,
    outsideHandler: null,
    repositionHandler: null,
    placeholder: config.placeholder || 'Select an option',
    teleportStyle: '',
    init() {
        if (!this.$refs.source || !this.$refs.input) return;

        this.buildOptions();
        this.setupObserver();
        this.setupInputObserver();
        this.setupGlobalHandlers();

        const current = this.normalizeValue(this.$refs.input?.value ?? '');
        if (current !== '') {
            this.value = current;
        } else {
            const preferred = this.options.find((option) => option.selected && !option.disabled);
            if (preferred) {
                this.value = preferred.value;
                this.pushValue(false);
            }
        }
    },
    destroy() {
        this.sourceObserver?.disconnect();
        this.inputObserver?.disconnect();

        if (this.$refs.input && this.syncValueFromInputBound) {
            this.$refs.input.removeEventListener('input', this.syncValueFromInputBound);
            this.$refs.input.removeEventListener('change', this.syncValueFromInputBound);
        }

        if (this.outsideHandler) {
            document.removeEventListener('pointerdown', this.outsideHandler);
        }

        if (this.repositionHandler) {
            window.removeEventListener('resize', this.repositionHandler);
            window.removeEventListener('scroll', this.repositionHandler, true);
        }
    },
    normalizeValue(value) {
        if (value === null || value === undefined) return '';
        return String(value);
    },
    buildOptions() {
        if (!this.$refs.source) return;
        const customNodes = Array.from(this.$refs.source.querySelectorAll('[data-admin-option]'));
        if (customNodes.length > 0) {
            this.options = customNodes.map((node, index) => ({
                key: `custom-${index}-${node.dataset.value ?? ''}`,
                value: this.normalizeValue(node.dataset.value ?? ''),
                label: (node.textContent || '').trim(),
                disabled: node.dataset.disabled === '1',
                selected: node.dataset.selected === '1',
            }));
            this.syncValueFromInput();
            return;
        }

        const nativeOptions = Array.from(this.$refs.source.querySelectorAll('option'));
        this.options = nativeOptions.map((node, index) => ({
            key: `native-${index}-${node.value ?? ''}`,
            value: this.normalizeValue(node.value ?? ''),
            label: (node.textContent || '').trim(),
            disabled: !!node.disabled,
            selected: !!node.selected,
        }));
        this.syncValueFromInput();
    },
    setupObserver() {
        if (!this.$refs.source) return;
        this.sourceObserver = new MutationObserver(() => this.buildOptions());
        this.sourceObserver.observe(this.$refs.source, { childList: true, subtree: true, characterData: true });
    },
    setupInputObserver() {
        if (!this.$refs.input) return;
        this.inputObserver = new MutationObserver(() => this.syncValueFromInput());
        this.inputObserver.observe(this.$refs.input, { attributes: true, attributeFilter: ['value'] });

        this.syncValueFromInputBound = () => this.syncValueFromInput();
        this.$refs.input.addEventListener('input', this.syncValueFromInputBound);
        this.$refs.input.addEventListener('change', this.syncValueFromInputBound);
    },
    setupGlobalHandlers() {
        this.outsideHandler = (event) => {
            if (!this.open) return;

            const target = event.target;
            const inRoot = this.$el?.contains(target);
            const inMenu = this.$refs.menu?.contains(target);
            if (inRoot || inMenu) return;

            this.close();
        };

        this.repositionHandler = () => {
            if (!this.open) return;
            this.positionMenu();
        };

        document.addEventListener('pointerdown', this.outsideHandler);
        window.addEventListener('resize', this.repositionHandler);
        window.addEventListener('scroll', this.repositionHandler, true);
    },
    syncValueFromInput() {
        if (!this.$refs.input) return;
        const incoming = this.normalizeValue(this.$refs.input.value ?? '');

        if (incoming === '') {
            this.value = '';
            return;
        }

        const hasMatch = this.options.some((option) => option.value === incoming);
        if (hasMatch) {
            this.value = incoming;
            return;
        }

        // Only clear if options have been populated and no option matches the incoming value
        if (this.options.length > 0) {
            this.value = '';
        }
    },
    isDisabled() {
        return !!this.$refs.input?.disabled;
    },
    selectedLabel() {
        const match = this.options.find((option) => option.value === this.value);
        return match ? match.label : this.placeholder;
    },
    selectedValue() {
        const match = this.options.find((option) => option.value === this.value);
        return match ? match.value : '';
    },
    toggle() {
        if (this.isDisabled()) return;
        if (this.open) {
            this.close();
            return;
        }

        this.open = true;
        this.$nextTick(() => this.positionMenu());
    },
    positionMenu() {
        const trigger = this.$refs.trigger;
        const menu = this.$refs.menu;
        if (!trigger || !menu) return;

        const triggerRect = trigger.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const viewportWidth = window.innerWidth;
        const edgeGap = 8;
        const menuHeight = Math.min(256, Math.max(160, menu.scrollHeight || 0));
        const spaceBelow = viewportHeight - triggerRect.bottom - edgeGap;
        const spaceAbove = triggerRect.top - edgeGap;
        const shouldOpenUp = spaceBelow < Math.min(menuHeight, 240) && spaceAbove > spaceBelow;

        let top = shouldOpenUp
            ? triggerRect.top - menuHeight - edgeGap
            : triggerRect.bottom + edgeGap;

        top = Math.max(edgeGap, Math.min(top, viewportHeight - edgeGap - 48));

        const width = Math.max(triggerRect.width, 140);
        const left = Math.max(edgeGap, Math.min(triggerRect.left, viewportWidth - edgeGap - width));

        this.teleportStyle = `top:${top}px;left:${left}px;width:${width}px;max-height:256px;`;
    },
    close() {
        this.open = false;
    },
    choose(option) {
        if (this.isDisabled() || option.disabled) return;
        this.value = option.value;
        this.pushValue(true);
        this.close();
    },
    pushValue(emitEvents = true) {
        if (!this.$refs.input) return;
        this.$refs.input.value = this.value;
        if (!emitEvents) return;
        this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
    },
});

window.westhubAdminDatePicker = (config = {}) => ({
    open: false,
    value: '',
    viewMonth: 0,
    viewYear: 0,
    weekdayLabels: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
    placeholder: config.placeholder || 'Select a date',
    minDateIso: config.min || null,
    maxDateIso: config.max || null,
    inputObserver: null,
    syncValueFromInputBound: null,
    init() {
        const today = new Date();
        this.viewMonth = today.getMonth();
        this.viewYear = today.getFullYear();

        this.setupInputObserver();
        this.syncValueFromInput();
    },
    destroy() {
        this.inputObserver?.disconnect();

        if (this.$refs.input && this.syncValueFromInputBound) {
            this.$refs.input.removeEventListener('input', this.syncValueFromInputBound);
            this.$refs.input.removeEventListener('change', this.syncValueFromInputBound);
        }
    },
    setupInputObserver() {
        if (!this.$refs.input) return;

        this.inputObserver = new MutationObserver(() => this.syncValueFromInput());
        this.inputObserver.observe(this.$refs.input, { attributes: true, attributeFilter: ['value'] });

        this.syncValueFromInputBound = () => this.syncValueFromInput();
        this.$refs.input.addEventListener('input', this.syncValueFromInputBound);
        this.$refs.input.addEventListener('change', this.syncValueFromInputBound);
    },
    normalizeIso(value) {
        if (value === null || value === undefined) return '';
        return String(value).trim();
    },
    parseIso(iso) {
        const normalized = this.normalizeIso(iso);
        const match = normalized.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!match) return null;

        const year = Number(match[1]);
        const month = Number(match[2]);
        const day = Number(match[3]);

        if (month < 1 || month > 12 || day < 1 || day > 31) return null;

        const date = new Date(year, month - 1, day);
        if (
            date.getFullYear() !== year ||
            date.getMonth() !== month - 1 ||
            date.getDate() !== day
        ) {
            return null;
        }

        return date;
    },
    toIso(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    },
    formatDisplay(iso) {
        const date = this.parseIso(iso);
        if (!date) return this.placeholder;
        return date.toLocaleDateString(undefined, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    },
    syncValueFromInput() {
        if (!this.$refs.input) return;
        const incoming = this.normalizeIso(this.$refs.input.value ?? '');
        this.value = incoming;

        const selectedDate = this.parseIso(incoming);
        if (selectedDate) {
            this.viewMonth = selectedDate.getMonth();
            this.viewYear = selectedDate.getFullYear();
        }
    },
    pushValue(emitEvents = true) {
        if (!this.$refs.input) return;

        this.$refs.input.value = this.value;
        if (!emitEvents) return;

        this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
        this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
    },
    monthLabel() {
        return new Date(this.viewYear, this.viewMonth, 1).toLocaleDateString(undefined, {
            month: 'long',
            year: 'numeric',
        });
    },
    moveMonth(step) {
        const candidate = new Date(this.viewYear, this.viewMonth + step, 1);
        this.viewMonth = candidate.getMonth();
        this.viewYear = candidate.getFullYear();
    },
    minDate() {
        return this.parseIso(this.minDateIso);
    },
    maxDate() {
        return this.parseIso(this.maxDateIso);
    },
    isWithinRange(date) {
        const min = this.minDate();
        const max = this.maxDate();
        if (min && date < min) return false;
        if (max && date > max) return false;
        return true;
    },
    days() {
        const firstDayOffset = new Date(this.viewYear, this.viewMonth, 1).getDay();
        const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        const cells = [];

        for (let index = 0; index < firstDayOffset; index += 1) {
            cells.push({
                key: `blank-start-${index}`,
                label: '',
                iso: null,
                disabled: true,
            });
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(this.viewYear, this.viewMonth, day);
            const iso = this.toIso(date);

            cells.push({
                key: iso,
                label: String(day),
                iso,
                disabled: !this.isWithinRange(date),
            });
        }

        const trailing = (7 - (cells.length % 7)) % 7;
        for (let index = 0; index < trailing; index += 1) {
            cells.push({
                key: `blank-end-${index}`,
                label: '',
                iso: null,
                disabled: true,
            });
        }

        return cells;
    },
    isToday(iso) {
        if (!iso) return false;
        return iso === this.toIso(new Date());
    },
    isSelected(iso) {
        return !!iso && iso === this.value;
    },
    selectDay(day) {
        if (!day.iso || day.disabled) return;
        this.value = day.iso;
        this.pushValue(true);
        this.open = false;
    },
    clear() {
        this.value = '';
        this.pushValue(true);
        this.open = false;
    },
    selectToday() {
        const today = new Date();
        if (!this.isWithinRange(today)) return;

        this.viewMonth = today.getMonth();
        this.viewYear = today.getFullYear();
        this.value = this.toIso(today);
        this.pushValue(true);
        this.open = false;
    },
    isDisabled() {
        return !!this.$refs.input?.disabled;
    },
    toggle() {
        if (this.isDisabled()) return;
        this.open = !this.open;
    },
});

window.addEventListener('DOMContentLoaded', () => {
    // Livewire pages boot Alpine through Livewire itself.
    // Non-Livewire pages (like login) still need a direct Alpine.start().
    if (!window.Livewire) {
        Alpine.start();
    }
});
