export function registerAssistant(Alpine) {
    Alpine.data('fsAssistant', () => ({
        open: false,
        busy: false,
        input: '',
        messages: [],
        emergency: false,
        error: '',
        endpoint: '',
        resetEndpoint: '',
        fallback: '',
        limitMessage: '',
        init() {
            this.endpoint = this.$el.dataset.assistantUrl || '';
            this.resetEndpoint = this.$el.dataset.assistantReset || '';
            this.fallback = this.$el.dataset.fallback || '';
            this.limitMessage = this.$el.dataset.limit || '';
        },
        toggle() {
            if (this.open) {
                this.close();

                return;
            }

            this.open = true;
            this.$nextTick(() => {
                this.$refs.composer?.focus();
            });
        },
        close() {
            this.open = false;
            this.$nextTick(() => {
                this.$refs.launcher?.focus();
            });
        },
        onEscape() {
            if (!this.open) {
                return;
            }

            if (this.$store.palette?.open || this.$store.cart?.open) {
                return;
            }

            this.close();
        },
        async send() {
            const message = this.input.trim();

            if (message === '' || this.busy) {
                return;
            }

            this.busy = true;
            this.error = '';
            this.input = '';
            this.messages.push({ role: 'user', text: message });
            this.messages.push({ role: 'assistant', text: '', pending: true, fallback: false });
            this.scrollThread();

            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        Accept: 'text/event-stream',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ message }),
                });

                if (response.status === 429) {
                    this.finishWith(this.limitMessage, true);

                    return;
                }

                if (!response.ok || !response.body) {
                    this.finishWith(this.fallback, true);

                    return;
                }

                await this.readStream(response);
            } catch {
                this.finishWith(this.fallback, true);
            } finally {
                this.busy = false;
                this.scrollThread();
            }
        },
        async readStream(response) {
            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { value, done } = await reader.read();

                if (done) {
                    break;
                }

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    this.applyLine(line);
                }
            }

            if (buffer.trim() !== '') {
                this.applyLine(buffer);
            }

            const current = this.currentReply();

            if (current?.pending) {
                this.finishWith(current.text || this.fallback, current.text === '');
            }
        },
        applyLine(line) {
            const trimmed = line.trim();

            if (!trimmed.startsWith('data:')) {
                return;
            }

            let event;

            try {
                event = JSON.parse(trimmed.slice(5).trim());
            } catch {
                return;
            }

            if (!event || typeof event !== 'object') {
                return;
            }

            const current = this.currentReply();

            if (event.type === 'emergency') {
                this.emergency = true;
            }

            if (!current) {
                return;
            }

            if (event.type === 'delta' && typeof event.text === 'string') {
                current.pending = false;
                current.text += event.text;
            }

            if ((event.type === 'replace' || event.type === 'fallback') && typeof event.text === 'string') {
                current.pending = false;
                current.fallback = event.type === 'fallback';
                current.text = event.text;
            }

            if (event.type === 'done' && current.pending) {
                current.pending = false;

                if (current.text === '') {
                    current.fallback = true;
                    current.text = this.fallback;
                }
            }

            this.scrollThread();
        },
        finishWith(text, fallback) {
            const current = this.currentReply();

            if (!current) {
                this.error = text;

                return;
            }

            current.pending = false;
            current.fallback = fallback;
            current.text = text;
        },
        currentReply() {
            return this.messages[this.messages.length - 1] || null;
        },
        async clearChat() {
            if (this.busy) {
                return;
            }

            this.error = '';

            try {
                const response = await fetch(this.resetEndpoint, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    this.error = 'The conversation could not be cleared.';

                    return;
                }

                this.messages = [];
                this.emergency = false;
            } catch {
                this.error = 'The conversation could not be cleared.';
            }
        },
        scrollThread() {
            this.$nextTick(() => {
                const thread = this.$refs.thread;

                if (thread) {
                    thread.scrollTop = thread.scrollHeight;
                }
            });
        },
    }));
}
