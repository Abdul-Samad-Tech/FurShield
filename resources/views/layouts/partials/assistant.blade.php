<div
    class="fixed right-4 z-[65] flex w-[min(24rem,calc(100vw-2rem))] flex-col items-end gap-fs-xs {{ $shell === 'dashboard' ? 'bottom-24 lg:bottom-4' : 'bottom-4' }}"
    style="padding-bottom: env(safe-area-inset-bottom)"
    x-data="fsAssistant"
    data-assistant-url="{{ route('owner.assistant.store') }}"
    data-assistant-reset="{{ route('owner.assistant.reset') }}"
    data-fallback="{{ config('assistant.fallback') }}"
    data-limit="{{ config('assistant.limit_message') }}"
    @keydown.escape.window="onEscape()"
>
    <section
        id="assistant-sheet"
        x-ref="panel"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.300ms
        role="dialog"
        aria-modal="false"
        aria-labelledby="assistant-title"
        class="assistant-panel flex max-h-[min(32rem,60dvh)] w-full flex-col rounded-fs-lg border border-border bg-surface-raised text-text shadow-fs-lg"
    >
        <div class="flex items-start justify-between gap-fs-sm border-b border-border px-fs-sm py-fs-sm">
            <div>
                <h2 id="assistant-title" class="font-display text-xl font-semibold text-text">FurShield Assistant</h2>
                <p id="assistant-disclaimer" class="mt-fs-3xs text-sm text-text" role="note">{{ config('assistant.disclaimer') }}</p>
            </div>
            <button type="button" class="grid size-11 shrink-0 place-items-center rounded-fs-sm text-text hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus" @click="close()" aria-label="Close assistant">
                <x-ui.icon name="x" />
            </button>
        </div>

        <div x-show="emergency" x-cloak class="border-b border-border bg-danger-soft px-fs-sm py-fs-sm text-text" role="alert">
            <p>This sounds urgent. Contact a veterinarian now.</p>
            <div class="mt-fs-xs flex flex-wrap gap-fs-xs">
                <x-ui.button variant="danger" :href="route('triage')">Emergency check</x-ui.button>
                @if ($emergencyTel)
                    <x-ui.button variant="secondary" :href="$emergencyTel">Call emergency line</x-ui.button>
                @endif
            </div>
        </div>

        <div x-ref="thread" class="flex-1 space-y-fs-xs overflow-y-auto px-fs-sm py-fs-sm" aria-live="polite">
            <p x-show="messages.length === 0" x-cloak class="text-sm text-text">Ask about feeding, a reminder, or what a record means. The answer uses your pet profiles and stays on this page.</p>
            <template x-for="(message, index) in messages" :key="index">
                <div>
                    <p x-show="message.role === 'user'" class="ml-auto max-w-[85%] rounded-fs-md bg-surface-sunken px-fs-sm py-fs-xs text-sm text-text" x-text="message.text"></p>
                    <div x-show="message.role === 'assistant' && message.pending" class="max-w-[85%]" role="status">
                        <span class="sr-only">The assistant is writing</span>
                        <span class="mb-fs-3xs block h-3 w-3/4 rounded-fs-xs bg-surface-sunken motion-safe:animate-pulse"></span>
                        <span class="block h-3 w-1/2 rounded-fs-xs bg-surface-sunken motion-safe:animate-pulse"></span>
                    </div>
                    <p
                        x-show="message.role === 'assistant' && !message.pending"
                        class="max-w-[85%] whitespace-pre-wrap rounded-fs-md border px-fs-sm py-fs-xs text-sm text-text"
                        :class="message.fallback ? 'border-danger/30 bg-danger-soft' : 'border-border bg-surface'"
                        :role="message.fallback ? 'alert' : 'presentation'"
                        x-text="message.text"
                    ></p>
                </div>
            </template>
            <p x-show="error" x-cloak class="rounded-fs-md border border-danger/30 bg-danger-soft px-fs-sm py-fs-xs text-sm text-text" role="alert" x-text="error"></p>
        </div>

        <form class="border-t border-border px-fs-sm py-fs-sm" @submit.prevent="send()">
            <x-ui.textarea
                label="Message"
                name="message"
                id="assistant-message"
                rows="3"
                x-ref="composer"
                x-model="input"
                maxlength="800"
                x-bind:disabled="busy"
                aria-describedby="assistant-disclaimer"
                required
            />
            <div class="mt-fs-xs flex flex-wrap gap-fs-xs">
                <x-ui.button type="submit" x-bind:disabled="busy">Send</x-ui.button>
                <x-ui.button type="button" variant="ghost" @click="clearChat()" x-bind:disabled="busy">Clear</x-ui.button>
            </div>
        </form>
    </section>

    <button
        type="button"
        x-ref="launcher"
        class="inline-flex min-h-11 items-center gap-fs-2xs rounded-fs-xl bg-success px-fs-md text-sm font-medium text-text-inverse shadow-fs-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus focus-visible:ring-offset-2 focus-visible:ring-offset-surface motion-safe:transition motion-safe:duration-fs-fast motion-safe:ease-fs-standard hover:bg-tide-deep"
        @click="toggle()"
        :aria-expanded="open ? 'true' : 'false'"
        aria-controls="assistant-sheet"
    >
        <x-ui.icon name="message-circle" />
        <span>Assistant</span>
    </button>
</div>
